<?php

namespace Modules\Cart\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Http\Requests\Admin\CartStoreRequest;
use Modules\Cart\Http\Requests\Admin\CartUpdateRequest;
use Shetabit\Shopit\Modules\Cart\Http\Controllers\Admin\CartController as BaseCartController;

class CartController extends Controller
{

    public function index()
    {
        $carts = Cart::withCommonRelations()->get();

        return response()->success('سبد خرید شما', compact('carts'));
    }

    /**
     * @param CartStoreRequest $request
     * @param Cart $cart
     * @return jsonResponse
     */
    public function add(CartStoreRequest $request, $varietyId): JsonResponse
    {
        $cart = new Cart();
        $cart->fill($request->all());
        #set variety in CartStoreRequest
        $cart->setDiscountPrice($request->variety);
        $cart->setPrice($request->variety);
        $cart->customer()->associate(auth()->user());
        $cart->variety()->associate($request->variety);
        $cart->save();
//        $cart->loadCommonRelations();   #if needed

        return response()->success('محصول با موفقیت به سبد خرید اضافه شد', compact('cart'));
    }


    public function update(CartUpdateRequest $request, Cart $cart)
    {
        $cart->quantity = $request->quantity;
        $cart->save();

        return response()->success('', compact('cart'));
    }

    public function remove(Cart $cart)
    {
        $cart->delete();

        return response()->success('محصول با موفقیت از سبد حذف شد');
    }
}
