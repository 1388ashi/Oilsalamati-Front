<?php

namespace Modules\Cart\Http\Controllers\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Http\Requests\Admin\CartStoreRequest;
use Modules\Cart\Http\Requests\Admin\CartUpdateRequest;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Variety;
use Modules\Setting\Entities\Setting;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Services\ShippingCollectionService;

class CartController extends Controller
{
    public function index()
    {
        $messages = [];
        /** @var Customer $user */
        $user = auth('customer')->user();
        $carts = $user->carts;
        $carts_showcase = $user->get_carts_showcase($carts);

        if (request()->header('Accept') === 'application/json') {
            foreach ($carts ?? [] as $cart)
                $cart->getReadyForFront();

            return response()->success('سبد خرید شما', compact('carts_showcase', 'carts','messages'/*, 'reservations'*/));
        }

        $shippings = (new ShippingCollectionService)->getActiveShippings();

        return view('cart::front.index', compact(['carts_showcase', 'carts', 'messages', 'user', 'shippings']));
    }

    public function checkFreeShipping()
    {
        $user = \Auth::user();
        $free_shipping = (new \Modules\Core\Helpers\Helpers)->getShippingAmountByOrderAmount($user->id);
        $has_free_shipping = [
            'result' => $free_shipping
        ];
        return response()->success('ارسال رایگان', compact('has_free_shipping'));
    }

    public function add(CartStoreRequest $request, $varietyId): JsonResponse
    {
        $varietyInCart = \Auth::user()->carts()->where('variety_id', $request->variety->id)->first();
        $default_max_limit = Setting::getFromName('default_product_max_limit') ? Setting::getFromName('default_product_max_limit') : 1000;

        if ($varietyInCart){
            $varietyInCart->quantity += $request->input('quantity');
        }

        $variety = Variety::find($request->variety->id);



        if (!$varietyInCart){
            if ($variety?->max_limit && $variety?->max_limit < $request->input('quantity')){
                return response()->error('تعداد بیشتر از حداکثر توان خرید است');
            }elseif($variety->product->max_limit && $variety->product->max_limit < $request->input('quantity')){
                return response()->error('تعداد بیشتر از حداکثر توان خرید است');
            }else{
                if ($default_max_limit < $request->input('quantity')){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }
        }


        if ($varietyInCart){
            if($varietyInCart?->variety->max_limit != null){
                if ($varietyInCart->quantity > $varietyInCart->variety->max_limit){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }elseif($varietyInCart?->variety->product->max_limit != null){
                if ($varietyInCart->quantity >  $varietyInCart->variety->product->max_limit){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }else{
                if ($default_max_limit < $varietyInCart->quantity ){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }
        }

        if ($varietyInCart){
            $varietyInCart->save();
            $user = \Auth::guard('customer-api')->user();

            $carts = $user->carts;
            $carts_showcase = $user->get_carts_showcase($user->carts);
            foreach ($carts ?? [] as $cart) $cart->getReadyForFront();

            return response()->success('تعداد محصول با موفقیت افزایش یافت', [
                'carts' => $carts,
                'carts_showcase' => $carts_showcase,
            ]);
        }

        $newCart = Cart::addToCart($request->input('quantity'), $request->variety, \Auth::user());
        $user = \Auth::guard('customer-api')->user();
        $carts = $user->carts;
        $carts_showcase = $user->get_carts_showcase($carts);
        foreach ($carts ?? [] as $cart) $cart->getReadyForFront();

        return response()->success('محصول موفقیت به سبد خرید اضافه شد', compact('newCart','carts_showcase','carts'));
    }

    public function update(CartUpdateRequest $request, $id)
    {
        $icart = Cart::find($id);
        $variety = Variety::find($icart->variety->id);
        $varietyInCart = \Auth::user()->carts()->where('variety_id', $variety->id)->first();
        $default_max_limit = Setting::getFromName('default_product_max_limit') ? Setting::getFromName('default_product_max_limit') : 1000;
        $isIncrement = $request->cart->quantity < $request->quantity;


        if ($varietyInCart){
            if($varietyInCart?->variety->max_limit != null){
                if ($varietyInCart->quantity > $varietyInCart->variety->max_limit -1 && $isIncrement){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }elseif($varietyInCart?->variety->product->max_limit != null){
                if ($varietyInCart->quantity >  $varietyInCart->variety->product->max_limit -1 && $isIncrement){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }else{
                if ($default_max_limit <= $varietyInCart->quantity -1 && $isIncrement){
                    return response()->error('تعداد بیشتر از حداکثر توان خرید است');
                }
            }
        }

        //cart set in request
        $request->cart->quantity = $request->quantity;
        $request->cart->save();
        $cart =  $request->cart;
        $cart->getReadyForFront();

        $user = \Auth::guard('customer-api')->user();
        $carts = $user->carts;
        $carts_showcase = $user->get_carts_showcase($carts);
        foreach ($carts as $cart) $cart->getReadyForFront();

        return response()->success(
            $isIncrement
                ? 'محصول موفقیت به سبد خرید اضافه شد'
                : 'محصول با موفقیت از سبد خرید کم شد'
            , compact(
                'carts',
                'carts_showcase',
            )
        );
    }

    public function remove(Cart $cart)
    {
        $cart->delete();

        if (request()->header('Accept') === 'application/json') {
            /** @var Customer $user */
            $user = Auth::guard('customer-api')->user();
        }else {
            $user = Auth::guard('customer')->user();
        }

        $carts = $user->carts;
        $carts_showcase = $user->get_carts_showcase($carts);
        
        if (request()->header('Accept') === 'application/json') {
            foreach ($carts as $cart) $cart->getReadyForFront();

            return response()->success('محصول با موفقیت از سبد حذف شد', compact('carts', 'carts_showcase'));
        }

        return redirect()->back()->with([
            'success' => 'محصول با موفقیت از سبد حذف شد'
        ]);
    }

}
