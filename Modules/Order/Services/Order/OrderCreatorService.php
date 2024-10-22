<?php

namespace Modules\Order\Services\Order;


use Illuminate\Database\Eloquent\Collection;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Services\CouponCalculatorService;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderGiftRange;
use Modules\Product\Entities\Product;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Services\ShippingCalculatorService;
use Modules\Store\Services\StoreBalanceService;

class OrderCreatorService
{
    public array $calculatorResponse;

    public function __construct(
        public $carts,
        public Customer $customer,
        public Address|null $address = null,
        public Shipping|null $shipping = null,
        public Coupon|null $coupon = null,
        public int $discount_on_order = 0,
        public $pay_type = null, /* attention: pay_type and payment_driver must be correct. we don't validate it here */
        public $payment_driver = null,
        public bool $byAdmin = false,
        public bool $couponServiceOnUpdateMode = false
    )
    {
//        if ($this->pay_type && in_array($this->pay_type, ['both', 'gateway']) &&
//            (!$this->payment_driver || !in_array($this->payment_driver, Payment::getAvailableDrivers())))
//        {
//            $this->pay_type = null;
//        }

//        if ($this->address->customer_id != $this->customer->id)
//            $this->address = null;
    }


    public function validator()
    {
        $this->checkBalance();
        $this->calculatorResponse = $this->calculator();
        if ($this->pay_type == 'wallet')
            $this->checkWallet();
    }

    public function calculator():array
    {
        $shipping_amount = 0;
        $shipping_packet_amount = 0;
        $shipping_more_packet_price = 0;
        $shipping_first_packet_size = 0;
        $orderWeight = 0;
        $discount_on_coupon = 0;
        $pay_by_gift_balance = 0;
        $pay_by_main_balance = 0;
        $pay_by_gateway = 0;
        // ============================================
        $carts = $this->carts;
        $total_items_amount = Cart::calculate_sum_carts($carts);
        // coupon =====================================
        if ($this->coupon) {
            $couponCalculatorServiceObject = new CouponCalculatorService($this->coupon,$carts,$total_items_amount,$this->customer,$this->couponServiceOnUpdateMode);
            $couponCalculatorServiceObject->validator();
            $discount_on_coupon = $couponCalculatorServiceObject->calculator()['discount'];
        }
        // ============================================
        $discount_on_items = Cart::calculate_sum_discounts_carts($carts);
        // ============================================
        if ($this->shipping && $this->address && $carts->count() != 0) {
            $shippingCalculatorServiceResponse = (new ShippingCalculatorService($this->address,$this->shipping,$this->customer,$carts))->calculate();
            $shipping_amount = $shippingCalculatorServiceResponse['shipping_amount'];
            $shipping_packet_amount = $shippingCalculatorServiceResponse['shipping_packet_amount'];
            $shipping_more_packet_price = $shippingCalculatorServiceResponse['shipping_more_packet_price'];
            $shipping_first_packet_size = $shippingCalculatorServiceResponse['shipping_first_packet_size'];
            $orderWeight = $shippingCalculatorServiceResponse['orderWeight'];
        }
        // ============================================
        $total_invoices_amount = $total_items_amount + $shipping_amount - $discount_on_coupon - $this->discount_on_order;
        // ============================================
        if ($this->pay_type) {
            [
                $pay_by_gift_balance,
                $pay_by_main_balance,
                $pay_by_gateway
            ] = Invoice::pay_calculator($total_invoices_amount,$this->pay_type,$this->customer,0);
        }
        // ============================================
        return [
            'discount_on_order' => $this->discount_on_order,
            'discount_on_coupon' => $discount_on_coupon,
            'discount_on_items' => $discount_on_items,
            'discount_total' => $discount_on_items + $this->discount_on_order + $discount_on_coupon,

            'shipping_amount' => $shipping_amount,
            'shipping_packet_amount' => $shipping_packet_amount,
            'shipping_first_packet_size' => $shipping_first_packet_size,
            'shipping_more_packet_price' => $shipping_more_packet_price,
            'weight' => $orderWeight,


            'total_items_amount_without_discount' => $total_items_amount + $discount_on_items,
            'total_items_amount' => $total_items_amount,
//            'products_prices_with_discount' => $sum_order_items_amount_with_discount,

            'total_quantity' => $total_invoices_amount,
            'total_items_count' => $total_invoices_amount,
            'total_invoices_amount' => $total_invoices_amount,

            'pay_by_wallet_gift_balance' => $pay_by_gift_balance,
            'pay_by_wallet_main_balance' => $pay_by_main_balance,
            'pay_by_gateway' => $pay_by_gateway
        ];
    }

    public function store($orderGift, $description, $reserved_at):Order
    {
        $ORDER = new Order();
        $order = new Order();

        /* todo: we should delete delivered_at field in orders table */
        $order->fill([
            'status' => Order::STATUS_WAIT_FOR_PAYMENT,
            'discount_amount' => 0,
            'discount_on_order' => $this->calculatorResponse['discount_on_order'],
            'discount_on_coupon' => $this->calculatorResponse['discount_on_coupon'],
            'discount_on_items' => $this->calculatorResponse['discount_on_items'],

            'shipping_amount' => $this->calculatorResponse['shipping_amount'],
            'shipping_packet_amount' => $this->calculatorResponse['shipping_packet_amount'],
            'shipping_first_packet_size' => $this->calculatorResponse['shipping_first_packet_size'],
            'shipping_more_packet_price' => $this->calculatorResponse['shipping_more_packet_price'],
            'weight' => $this->calculatorResponse['weight'],

            'total_items_amount' => $this->calculatorResponse['total_items_amount'],
            'total_quantity' => $this->calculatorResponse['total_quantity'],
            'total_items_count' => $this->calculatorResponse['total_items_count'],
            'total_invoices_amount' => $this->calculatorResponse['total_invoices_amount'],

            'pay_by_wallet_gift_balance' => $this->calculatorResponse['pay_by_wallet_gift_balance'],
            'pay_by_wallet_main_balance' => $this->calculatorResponse['pay_by_wallet_main_balance'],

            'shipping_id' => $this->shipping->id,
            'coupon_id' => $this->coupon ? $this->coupon->id : null,
            'address' => $this->address->toJson(),
            'reserved_at' => $reserved_at,
            'gift_title'=>$orderGift->title ?? null,
            'gift_price'=>$orderGift->price ?? null,
            'description' => $description,
        ]);

        $order->customer()->associate($this->customer);
        $order->address()->associate($this->address);
        $order->save();

        // store items for this order
        foreach ($this->carts as $cart) {
            $ORDER->addItemsInOrder($order, $cart);
        }
        $order->load('items');
        return $order;
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function checkBalance():void
    {
        foreach ($this->carts as $cart) {
            $variety = $cart->variety;
            $productCache = (new ProductsCollectionService())->getProductObjectFromVarietyId($variety->id);
            // check variety balance ================
//            if ($variety->store->balance < $cart->quantity)
            if (!(new StoreBalanceService($variety->id))->checkBalance($cart->quantity))
                throw Helpers::makeValidationException('تعداد انتخاب شده محصول ' . $productCache->title . ' بیشتر از موجودی انبار است.');
            // check product status admin can sell products with STATUS_AVAILABLE and STATUS_AVAILABLE_OFFLINE. while customers can just buy STATUS_AVAILABLE
            if ($this->byAdmin) {
                if (!in_array($productCache->status,[Product::STATUS_AVAILABLE, Product::STATUS_AVAILABLE_OFFLINE]))
                    throw Helpers::makeValidationException(' محصول ' . $productCache->title . ' در وضعیت قابل فروش نیست است. ','status');
            } else {
                if ($productCache->status != Product::STATUS_AVAILABLE)
                    throw Helpers::makeValidationException(' محصول ' . $productCache->title . ' ناموجود است. ','status');
            }
            // check max_number_purchases. this is just for Customer. admin can sell every thing ================
            if (!$this->byAdmin) {
                if ($cart->variety->max_number_purchases < $cart->quantity) {
                    throw Helpers::makeValidationException(
                        "شما از تنوع {$productCache->title} فقط میتوانید {$cart->variety->max_number_purchases} خرید کنید",
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

    private function checkWallet():void{
        if ($this->customer->balance < $this->calculatorResponse['total_invoices_amount'])
            throw Helpers::makeValidationException('موجودی کیف پول کافی نیست');
    }



}
