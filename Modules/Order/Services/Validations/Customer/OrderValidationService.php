<?php

namespace Modules\Order\Services\Validations\Customer;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Entities\Admin;
use Modules\Cart\Entities\Cart;
use Modules\Category\Entities\Category;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\SafeArray;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Services\CalculateCouponDiscountService;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Order\Classes\OrderStoreProperties;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Setting\Entities\Setting;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Services\ShippingCalculatorService;
use Modules\Store\Services\StoreBalanceService;

//use Shetabit\Shopit\Modules\Order\Services\Validations\Customer\OrderValidationService as BaseOrderValidationService;

class OrderValidationService/* extends BaseOrderValidationService*/
{
    protected SafeArray $request;
    protected Customer $customer;
    public OrderStoreProperties $properties;

    public function __construct(
        array $request,
        Customer $customer,
        public $byAdmin = false,
        $defaultPriceFieldInFinalPrice = 'amount'
    )
    {
        $this->properties = new OrderStoreProperties();
        $this->request = new SafeArray($request);
        $this->customer = $customer;

        if ($this->byAdmin) {
            // so varieties are not object of Variety class it is just id. so we should convert it
            // we create fake cart.
            $fakeCarts = [];
            foreach ($this->request['varieties'] as $requestVariety) {
                $variety = Variety::findOrFail($requestVariety['id']);
                /* todo: because of DontAppend method in final_price method in Variety, we have to load product to have final_price attribute here. */
                $variety->load('product');
                $newFakeCart = new Cart([
                    'variety_id' => $requestVariety['id'],
                    'quantity' => $requestVariety['quantity'],
                    'discount_price' => $variety->final_price['discount_price'],
                    'price' => $variety->final_price['amount'],
                ]);
                $fakeCarts[] = $newFakeCart;
            }
            $carts = collect($fakeCarts);
            $this->properties->carts = $carts;
            $this->properties->customer = $customer;
        } else {
            /* todo: also here. we are force to load product because of final_price method in Variety */
            $carts = $customer->carts()->with('variety.product')->get();
            if ($carts->count() < 1) throw Helpers::makeValidationException('سبد خرید شما خالی است!');

            $this->properties->carts = $carts;
            $this->properties->customer = $customer;
        }


        $this->checkAll();
    }


    public function checkAll()
    {
        $carts = $this->properties->carts;

        $this->checkBalanceAvailabality($carts);


//        if ($this->byAdmin){
//            $this->checkAdminVarieties();
//            $this->checkAvailableVariety($this->varieties);
//
//        }else{
//            foreach ($carts as $cart) {
//                $this->checkMaxQuantityVariety($cart);
//                $this->checkAvailableVariety($cart->variety);
//                $this->checkPrice($cart);
//            }
//        }

        $this->checkShipping();
        $this->checkDiscountAmount();
//        $this->properties->carts_showcase = $this->properties->customer->get_carts_showcase(
//            carts: $carts,
//            discount_on_coupon:  $this->properties->discount_amount,
//            shipping_amount:  $this->properties->shipping_amount
//        );

        $this->checkWallet();
//        if (!$this->byAdmin) {
//            if ($this->request['pay_wallet']) {
//                $this->checkWallet();
//            }
//        }
    }

    private function checkBalanceAvailabality($carts)
    {
        foreach ($carts as $cart) {
            $variety = $cart->variety;
            // check variety balance ================
//            if ($variety->store->balance < $cart->quantity)
            if (!(new StoreBalanceService($variety->id))->checkBalance($cart->quantity))
                throw Helpers::makeValidationException('تعداد انتخاب شده محصول ' . $variety->product->title . ' بیشتر از موجودی انبار است.');
            // check product status admin can sell products with STATUS_AVAILABLE and STATUS_AVAILABLE_OFFLINE. while customers can just buy STATUS_AVAILABLE
            if ($this->byAdmin) {
                if (!in_array($variety->product->status,[Product::STATUS_AVAILABLE, Product::STATUS_AVAILABLE_OFFLINE]))
                    throw Helpers::makeValidationException(' محصول ' . $variety->product->title . ' در وضعیت قابل فروش نیست است. ','status');
            } else {
                if ($variety->product->status != Product::STATUS_AVAILABLE)
                    throw Helpers::makeValidationException(' محصول ' . $variety->product->title . ' ناموجود است. ','status');
            }
            // check max_number_purchases. this is just for Customer. admin can sell every thing ================
            if (!$this->byAdmin) {
                if ($cart->variety->max_number_purchases < $cart->quantity) {
                    throw Helpers::makeValidationException(
                        "شما از تنوع {$cart->variety->product->title} فقط میتوانید {$cart->variety->max_number_purchases} خرید کنید",
                        'variety_quantity'
                    );
                }
            }
            // check price ===========================
            if ($cart->price != $variety->final_price['amount'])
                throw Helpers::makeValidationException('قیمت محصول تغییر کرد');
            if ($cart->discount_price != $variety->final_price['discount_price'])
                throw Helpers::makeValidationException('قیمت محصول تغییر کرد');
        }
    }




    public function checkShipping()
    {
        /* @var $address Address */
        $address = $this->properties->customer->addresses()->findOrFail($this->request['address_id']);
        if (!$address)
            throw Helpers::makeValidationException('آدرس انتخاب شده نامعتبر است!', 'address_id');
        $shipping = Shipping::query()->active()->findOrFail($this->request['shipping_id']);

//        $deliveringDiffDays = Carbon::now()->diff($this->request['delivered_at'])->days;
//
//        if ($deliveringDiffDays < $shipping->minimum_delay || $deliveringDiffDays > 10) {
//            #TODO read max_delay in settings
//            throw Helpers::makeValidationException('تاریخ تحویل انتخاب شده نامعتبر است.', 'shipping_id');
//        }jh
        $customer = $this->properties->customer;
        $shippingServiceResponse = (new ShippingCalculatorService($address, $shipping, $customer, $this->properties->carts))->calculate();

        $this->properties->shipping_amount = $shippingServiceResponse['shipping_amount'] ?? 0;
        $this->properties->orderWeight = $shippingServiceResponse['orderWeight'] ?? 0;
        $this->properties->address = $address;
        $this->properties->shipping = $shipping;
        $this->properties->shipping_packet_amount = $shippingServiceResponse['shipping_packet_amount'];

    }

    private function checkDiscountAmount()
    {
        $discount_amount = 0;
        $coupon = null;
        if ($this->byAdmin) {
            $discount_amount = $this->request['discount_amount'];
        } else {
            if ($this->request['coupon_code']) {
                $carts = $this->properties->carts;
                $couponCalculatorService = new CalculateCouponDiscountService($this->request['coupon_code'], carts: $carts);
                $discount_amount = $couponCalculatorService->calculate()['discount'];
                $coupon = $couponCalculatorService->getCouponModel();
            }
        }
        $this->properties->discount_amount = $discount_amount;
        $this->properties->coupon = $coupon;
    }


    public function checkWallet()
    {
        $pay_half_active = app(CoreSettings::class)->get('invoice.pay_half.active');
        if (($pay_half_active || auth()->user() instanceof Admin)
            &&
            ($this->customer->balance < $this->properties->carts_showcase['payable_price'])) {
            throw Helpers::makeValidationException('موجودی کیف پول شما کافی نیست');
        }
    }



// DELETED CODES =======================================================================================================
// DELETED CODES =======================================================================================================

// DELETED CODES =======================================================================================================
// DELETED CODES =======================================================================================================






    private function sumOrderItemsCalculator($defaultPriceFieldInFinalPrice) : int
    {
        $total = 0;
        foreach (\request()->carts as $cart) {
            $total += ($cart->quantity * $cart->variety->final_price[$defaultPriceFieldInFinalPrice]);
        }
        return $total;
    }


    public function getShippingAmount()
    {
        $shipping = \request()->shipping;
        $this->properties->shipping = $shipping;

        $shippingCalculatorService = new ShippingCalculatorService(\request()->address,$shipping, \request()->customer);
        foreach (\request()->carts as $cart) {
            $shippingCalculatorService->addItem($cart);
        }
        $shippingCalculatorServiceResponse = $shippingCalculatorService->calculate();
        $this->properties->shipping_packet_amount = $shippingCalculatorServiceResponse['shipping_packet_amount'];
        $this->shippingAmount = $shippingCalculatorServiceResponse['shipping_amount'];

        $sumOrderItemsWithShipping = \request()->sumOrderItems + $this->shippingAmount;
        \request()->merge(['sumOrderItemsWithShipping' => $sumOrderItemsWithShipping]);

        return $this->shippingAmount;


        // old codes.
        /*if ($this->shippingAmount) {
            return $this->shippingAmount;
        }

        //calculate Shipping Amount

            $city = $this->getAddress()->city;

            if($city->id == 264 && $this->getSumCartsPriceWithoutShipping() >= Setting::getFromName('free_shipping_amount_for_gorgan')){
                $this->properties->shipping_packet_amount = 0;
                $this->shippingAmount =0;

            }elseif($this->getSumCartsPriceWithoutShipping() >= Setting::getFromName('free_shipping_amount_for_other_cities')){
                $this->properties->shipping_packet_amount = 0;
                $this->shippingAmount = 0;

            }else{
                $this->properties->shipping_packet_amount = $shipping->getPrice($city, $this->getOrderWeight(), customer_id: $this->request['customer_id']);

                $this->shippingAmount = $shipping->getPrice($city, $this->getOrderWeight(), $this->totalQuantity, customer_id: $this->request['customer_id']);
            }

            $carts = ($this->byAdmin) ? $this->checkSumPriceWhenAdmin() :  $this->customer->carts;

            // if there was a free_shipping product in carts, shipping_amount is free. this is a feature in 2220 ticket
            if ($this->byAdmin) {
                $varietyIds = [];
                foreach ($this->request['varieties'] as $varietyRequest) {
                    $varietyIds[] = $varietyRequest['id'];
                }
                if (Cart::has_free_shipping_product_by_variety_ids($varietyIds)) {
                    $this->shippingAmount = 0;
                    $this->properties->shipping_packet_amount = 0;
                    return $this->shippingAmount;
                }
            } else {
                if (Cart::has_free_shipping_product($carts)) {
                    $this->shippingAmount = 0;
                    $this->properties->shipping_packet_amount = 0;
                    return $this->shippingAmount;
                }
            }


            $overweight_shipping_amount = Setting::getFromName('overweight_shipping_amount') ? : 999;
            $money_overweight_shipping_amount = Setting::getFromName('money_overweight_shipping_amount') ? : 5000;

            if ($shipping->free_shipping == 1){
                $money_overweight_shipping_amount =0;
            }

            $over =0;
            foreach ($carts as $cart) {
                if($cart->variety?->weight > $overweight_shipping_amount){
                    $over++;
                }elseif($cart->variety->product?->weight > $overweight_shipping_amount){
                    $over++;
                }
            }

            if ($over && ($this->getSumCartsPriceWithoutShipping() < (Setting::where('name', '=', 'max_cart_price_money_overweight_shipping_amount')->first()?->value ?? 500000))){
                $this->shippingAmount += $money_overweight_shipping_amount;
            }

            return $this->shippingAmount;*/

    }






    // came from vendor ================================================================================================




    //تخفیف اعمال شده

    public function checkSumPriceWhenAdmin(): Collection
    {
        $carts = collect();
        foreach ($this->varieties as $variety) {
            $findVariety = Variety::query()->with(['product'])->find($variety['id']);
            $carts->push((object)[
                'price' => $findVariety->final_price['amount'],
                'quantity' => $variety['quantity']
            ]);
        }

        return $carts;
    }

    public function getRawSumCartsPrice(): float|int
    {
        $carts = ($this->byAdmin) ? $this->checkSumPriceWhenAdmin() :  $this->customer->carts;

        $sumCartsPrice = 0;
        foreach ($carts as $cart) {
            $sumCartsPrice += ($cart->price * $cart->quantity);
        }

        return $sumCartsPrice;
    }




    public function checkMaxQuantityVariety($cart)
    {
        if ($cart->variety->store->balance < $cart->quantity) {
            throw Helpers::makeValidationException(
                'تعداد انتخاب شده محصول ' . $cart->variety->product->title . ' بیشتر از موجودی انبار است.',
                'variety_quantity'
            );
        }
        if ($cart->variety->max_number_purchases < $cart->quantity) {
            throw Helpers::makeValidationException(
                "شما از تنوع {$cart->variety->product->title} فقط میتوانید {$cart->variety->max_number_purchases} خرید کنید",
                'variety_quantity'
            );
        }

        $this->totalQuantity += $cart->quantity;
    }

    public function checkAdminVarieties()
    {
        foreach (\request()->carts as $cart) {
            $variety = $cart->variety;
            if ($variety->store->balance < $cart->quantity) {
                throw Helpers::makeValidationException(
                    'تعداد انتخاب شده محصول ' . $variety->product->title . ' بیشتر از موجودی انبار است.',
                    'variety_quantity'
                );
            }
            $this->totalQuantity += $cart->quantity;
        }
        // old codes
        /*$varietyArray = $this->varieties ?: false;
        $varietyIds = collect($varietyArray)->pluck('id');
        $varieties = Variety::query()->whereIn('id', $varietyIds->toArray())->get();
        if (count($varietyIds) != count($varieties)){
            throw Helpers::makeValidationException('شناسه تنوع های ارسال شده نامعتبر است');
        }*/

        /*foreach ($varietyArray as $key => $variety) {
            $baseVariety = $varieties[$key];
            if ($baseVariety->store->balance < $variety['quantity']) {
                throw Helpers::makeValidationException(
                    'تعداد انتخاب شده محصول ' . $baseVariety->product->title . ' بیشتر از موجودی انبار است.',
                    'variety_quantity'
                );
            }
            $this->totalQuantity += $variety['quantity'];
        }*/
    }

    public function checkAvailableVariety($variety)
    {
        if ($this->byAdmin) {
            $varietyArray = $variety ?: false;
            $varietyIds = collect($varietyArray)->pluck('id');
            foreach ($varietyIds as $id) {
                $variety = Variety::findOrFail($id);
                if (!in_array($variety->product->status,[Product::STATUS_AVAILABLE, Product::STATUS_AVAILABLE_OFFLINE])) {
                    throw Helpers::makeValidationException(' محصول ' . $variety->product->title . ' در وضعیت قابل فروش نیست است. ','status');
                }
            }
        } else {
            if ($variety->product->status !== Product::STATUS_AVAILABLE) {
                throw Helpers::makeValidationException(' محصول ' . $variety->product->title . ' ناموجود است. ','status');
            }
        }
    }

    public function checkPrice(Cart $cart)
    {
        $variety = $cart->variety;
        if ($cart->price != $variety->final_price['amount']){
            throw Helpers::makeValidationException('قیمت محصول تغییر کرد');
        }
        if ($cart->discount_price != $variety->final_price['discount_price']){
            throw Helpers::makeValidationException('قیمت محصول تغییر کرد');
        }
    }

    public function getDiscountAmount()
    {
        if ($this->discountAmount) {
            return $this->discountAmount;
        }
        $sumCartsPrice = \request('sumOrderItems');
        $discount = 0;
        if ($this->request['coupon_code']) {
            $coupon = Coupon::where('code', $this->request['coupon_code'])->first();
            if (! $coupon) {
                throw Helpers::makeValidationException('کوپن انتخاب شده موجود نیست!', 'shipping_id');
            }
            $discount = (new CalculateCouponDiscountService($coupon->code, $sumCartsPrice))->calculate()['discount'];
            $this->properties->coupon = $coupon;

            Coupon::dontAllowCouponAndDiscountTogether();
        }
        if (auth()->user() instanceof Admin){
            return $this->discountAmount = $this->request['discount_amount'];
        }

        return $this->discountAmount = $discount;
    }

    public function getAddress()
    {
        if ($this->customerAddress) {
            return $this->customerAddress;
        }
        return $this->customerAddress = $this->customer->addresses->where('id', $this->request['address_id'])->first();
    }


    /*public function getOrderWeight()
    {
        //get card
        $carts = ($this->byAdmin) ? $this->checkSumPriceWhenAdmin() :  $this->customer->carts;
        $weight = 0;
        $iweight = Setting::getFromName('defualt_product_weight') ? Setting::getFromName('defualt_product_weight') : 120;

        if ($this->byAdmin) {
            foreach ($this->request['varieties'] as $varietyRequest) {
                $variety = Variety::find($varietyRequest['id']);
                if($variety?->weight){
                    $iweight = $variety->weight;
                }elseif($variety->product?->weight){
                    $iweight = $variety->product->weight;
                }

                $weight = $weight + ($varietyRequest['quantity'] * $iweight);
            }
        } else {
            //Get Card Weight
            foreach ($carts as $cart) {
                if($cart->variety?->weight){
                    $iweight = $cart->variety->weight;
                }elseif($cart->variety->product?->weight){
                    $iweight = $cart->variety->product->weight;
                }

                $weight = $weight + ($cart->quantity * $iweight);
            }
        }


        return $weight;
    }


    public function getSumCartsPrice()
    {
        if ($this->sumCartsPrice) {
            return $this->sumCartsPrice;
        }
        if (\Auth::user() instanceof Admin) {
            $sumCartsPrice = 0;
            foreach ($this->request['varieties'] as $v) {
                $variety = Variety::query()->withCommonRelations()->findOrFail($v['id']);
                $sumCartsPrice += $variety->final_price['amount'] * $v['quantity'];
            }
            return $sumCartsPrice;
        }
        $carts = $this->customer->carts;

        $sumCartsPrice = 0;
        foreach ($carts as $cart) {
            $sumCartsPrice += ($cart->price * $cart->quantity);
        }

        return $this->sumCartsPrice = ($sumCartsPrice - $this->getDiscountAmount() + $this->getShippingAmount());
    }
    public function getSumCartsPriceWithoutShipping(): float|int
    {

        $carts = ($this->byAdmin) ? $this->checkSumPriceWhenAdmin() :  $this->customer->carts;

        if ($this->sumCartsPriceWithoutShipping) {
            return $this->sumCartsPriceWithoutShipping;
        }

        $sumCartsPriceWithoutShipping = 0;
        foreach ($carts as $cart) {
            $sumCartsPriceWithoutShipping += ($cart->price * $cart->quantity);
        }
        return $this->sumCartsPriceWithoutShipping = ($sumCartsPriceWithoutShipping - $this->getDiscountAmount());
    }*/


}
