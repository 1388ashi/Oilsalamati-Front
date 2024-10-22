<?php

namespace Modules\Order\Services\Order;

use Illuminate\Database\Eloquent\Collection;
use Modules\Admin\Entities\Admin;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderItem;
use Modules\Order\Entities\OrderUpdater;
use Modules\Product\Entities\Product;
use Modules\Product\Services\ProductsCollectionService;
//use Modules\Store\Entities\Store;
use Modules\Store\Services\StoreBalanceService;

/* todo: we should write a description for this service */

/* Service Description.
 | hi. this is a service to update an order.
 | we 4 operation in this service: 1.add_items 2.delete_items 3.update_order_address 4.update_shipping. exactly same as OrderUpdater model. and
 | each operation has 3 methods call:
 |      1.validation_add_items:
 |          here we validate data, calculate many parameters, response errors, etc. and also we show these parameters in showcaseController.
 |      2.add_items
 |          here we make invoice with attention to the pay_type. and if the process has completed we response true and call add_items_success method to do those operations
 |      3.add_items_success
 |          here we do operations. for example, we add new items into the order. or delete items or update_order_address or update_shipping
 | we use of this service in two places.
 |      1. OrderUpdaterServiceController
 |          this is for updating order
 |      2. OrderUpdaterServiceShowcaseController
 |          this is for showing the changes of an update.
 |
 | in particularly, there exists two kind of errors in validation methods. first is main error. for example, order must be status=new or don't choose
 | gateway for customer guard. but for second type of errors that it doesn't need to response error. for example, not enough balance in wallet. we don't
 | need to show this error in showcase. so in showcase wantError attribute must be false
 |
 | what is OrderUpdater model? let wants to add 3 items to customer's order and there wasn't enough balance in wallet. with this model it can create a link
 | to customer can pay in gateway. also, in front of website we can send customer to the gateway for this edit.
 | therefore in admin panel we shouldn't send payment_driver, and we create just link. and in front of website we don't create link, we redirect customer to the gateway
 | we have followed these points in our codes. this service if payment_driver is null it returns link and if payment_driver is not null we redirect to the gateway

 | example of response of validation_add_items:
       "validatorResponse": {
            "shipping_differences": 0,
            "new_shipping_amount": 0,
            "coupon_discount_differences": 0,
            "new_coupon_discount_amount": 0,
            "new_order_weight": 7360,
            "items_differences": 795000,
            "invoice_amount": 795000,
            "pay_by_gift_balance": 0,
            "pay_by_main_balance": 720500,
            "pay_by_gateway": 74500
        }

 | example of response of add_items:
        $serviceResponse = [
            'has_process_completed' => false,
            'redirect_to_gateway' => false,
        ];

 | attention: you have to check examples of this service usage to understand completely in OrderUpdaterServiceShowcaseController and OrderUpdaterServiceController
 |
 | attention: in this service, every number was negative it means that we should return back this number to the customer
 |      positive numbers means we should get this amount from customer. and if was negative we should return it back to the customer
 |
 | we show the history of OrderUpdater in OrderUpdaterController
 | we update all items on parentOrder. we don't add child to the order. we update just order. we are force to get children items. because this structure were in the project
 |
 |
 * */

class OrderUpdaterService
{
    public Collection $allOrderItems;
    private Order $parentOrder;
    private array $validatorResponse;



    public function __construct (
        public $order,
        public $pay_type = 'wallet',
        public $payment_driver = null,
        /* $wantError variable is just for orderUpdaterShowcase routes. because in these routes we just wants calculations. in store routes it must be true */
        public bool $wantError = true
    )
    {
        // validate pay_type ============================
        if (!in_array($this->pay_type, ['wallet', 'gateway', 'both']))
            throw Helpers::makeValidationException('شیوه پرداخت اشتباه است');
        // attention. only in front of website you can use of redirecting to the gateway
        if ($this->payment_driver && !in_array($this->payment_driver, Payment::getAvailableDrivers()))
            throw Helpers::makeValidationException('درگاه پرداخت به درستی انتخاب نشده است');
        if (in_array($this->pay_type, ['both','gateway']) && !$this->payment_driver && !auth()->user() instanceof Admin && $this->wantError)
            throw Helpers::makeValidationException('لطفا درگاه پرداخت را انتخاب کنید');
        // we get all items =============================
        $this->allOrderItems = new Collection();
        if ($this->order->parent_id) {
            // so this we should get parent order to get all items (i.e. items of parent and also items of children)
            $parentOrder = $this->order->parent;
        } else $parentOrder = $this->order;
        $this->parentOrder = $parentOrder;
        foreach ($parentOrder->items()->active()->get() as $orderItem) {
            $this->allOrderItems->push($orderItem);
        }
        foreach ($parentOrder->childs as $child) {
            foreach ($child->items()->active()->get() as $orderItem) {
                $this->allOrderItems->push($orderItem);
            }
        } /* ============================================== */
    }

    public function validator($addCarts = null, $deleteCarts = null, $newAddress = null, $newShipping = null)
    {
        if (!$addCarts && !$deleteCarts && !$newAddress && !$newShipping)
            throw Helpers::makeValidationException("هیچگونه ویرایشی صورت نگرفته است");
        $this->general_order_validations_for_update_order();

        // validate carts in add mode. insert them to the orderItems collection
        foreach ($addCarts ?? [] as $cart) {
            if (!(new StoreBalanceService($cart->variety_id))->checkBalance($cart->quantity))
                throw Helpers::makeValidationException('موجودی محصول به پایان رسیده است.');

            $productCache = (new ProductsCollectionService())->getProductObjectFromVarietyId($cart->variety_id);
            if ($productCache->status != Product::STATUS_AVAILABLE)
                throw Helpers::makeValidationException("وضعیت محصول تایید شده نیست");
            // prepare allOrderItems with new Items. if the new item was exists to the order we just increase quantity
            if ($existItem = $this->allOrderItems->where('variety_id', $cart->variety_id)->first()) {
                $existItem->quantity += $cart->quantity;
            } else {
                // we create fake OrderItem to add in allOrderItems
                $newOrderItem = new OrderItem([
                    'product_id' => $productCache->id,
                    'variety_id' => $cart->variety_id,
                    'quantity' => $cart->quantity,
                    'amount' => $cart->price,
                    'status' => 1,
                    'order_id' => $this->parentOrder->id,
                    'discount_amount' => $cart->discount_price,
                    'extra' => collect([
                        'attributes' => $cart->variety->attributes()->get(['name', 'label', 'value']),
                        'color' => $cart->variety->color()->exists() ? $cart->variety->color->name : null
                    ])->toJson(),
//                    'flash_id' => $cart->variety->product->activeFlash->first()->id ?? null, /* todo: we should delete flash structure */
                ]);
                $this->allOrderItems->push($newOrderItem);
            }
        }
        // delete items ====================================================================
        $reduceItemDifferences = 0;
        foreach ($deleteCarts ?? [] as $cart) {
            $order_item = $this->allOrderItems->where('variety_id', $cart->variety_id)->first();
            if (!$order_item)
                throw Helpers::makeValidationException("آیتم انتخاب شده در سفارش موجود نیست");
            if ($order_item->quantity < $cart->quantity)
                throw Helpers::makeValidationException("تعداد حذف شده بیشتر از تعداد موجود در سفارش است.");
            elseif ($order_item->quantity == $cart->quantity) {
                $order_item->status = false;
                $reduceItemDifferences += ($order_item->amount * $cart->quantity);
            }
            else {
                $order_item->quantity -= $cart->quantity;
                $reduceItemDifferences += ($order_item->amount * $cart->quantity);
            }
        }
        if ($this->allOrderItems->where('status', '=', true)->count() <= 0)
            throw Helpers::makeValidationException("نمی توانید تمامی آیتم ها را از سفارش حذف کنید. سفارش را لغو کنید");
        // create fakeCart from order_items
        $fakeCartsOfOrderNewItems = Cart::fakeCartMakerWithOrderItems($this->allOrderItems);
        // address ====================================
        $address = $this->parentOrder->address()->first();
        if ($newAddress)
            $address = $newAddress;
        // shipping ===================================
        $shipping = $this->parentOrder->shipping;
        if ($newShipping && (!$newShipping->checkShippableAddress($address->city) || !$newShipping->status))
            throw Helpers::makeValidationException('شیوه ارسال انتخاب شده نامعتبر است.');

        $coupon = $this->parentOrder->coupon; /* todo: we have a bug here. usage of coupon has a limitation on usage */
        // we calculate this order with new items with OrderCreatorService calculator() method
        $orderCreatorServiceObject = new OrderCreatorService(
            carts: $fakeCartsOfOrderNewItems,
            customer: $this->parentOrder->customer,
            address: $address,
            shipping: $shipping,
            coupon: $coupon,
            discount_on_order: $this->discount_on_order ?? 0,
            pay_type: $this->pay_type,
            payment_driver: $this->payment_driver,
            couponServiceOnUpdateMode: true
        );

        $orderCalculatorResponse = $orderCreatorServiceObject->calculator();
        // calculate new shipping all amounts
        $validatorResponse = [
            'discount_on_order' => $orderCalculatorResponse['discount_on_order'],
            'discount_on_coupon' => $orderCalculatorResponse['discount_on_coupon'],
            'discount_on_coupon_differences' => $this->parentOrder->discount_on_coupon - $orderCalculatorResponse['discount_on_coupon'],
            'discount_on_items' => $orderCalculatorResponse['discount_on_coupon'],

            'shipping_amount' => $orderCalculatorResponse['shipping_amount'],
            'shipping_amount_differences' => $orderCalculatorResponse['shipping_amount'] - $this->parentOrder->total_shipping_amount,
            'shipping_packet_amount' => $orderCalculatorResponse['shipping_packet_amount'],
            'shipping_first_packet_size' => $orderCalculatorResponse['shipping_first_packet_size'],
            'shipping_more_packet_price' => $orderCalculatorResponse['shipping_more_packet_price'],
            'weight' => $orderCalculatorResponse['weight'],

            'total_items_amount' => $orderCalculatorResponse['total_items_amount'],
            'total_items_amount_differences' => $orderCalculatorResponse['total_items_amount'] - $this->parentOrder->total_items_amount,

            'total_quantity' => $orderCalculatorResponse['total_quantity'],
            'total_items_count' => $orderCalculatorResponse['total_items_count'],
            'total_invoices_amount' => $orderCalculatorResponse['total_invoices_amount'],
            'total_invoices_amount_differences' => $orderCalculatorResponse['total_invoices_amount'] - $this->parentOrder->total_invoices_amount,

            'pay_by_wallet_gift_balance' => $orderCalculatorResponse['pay_by_wallet_gift_balance'],
            'pay_by_wallet_main_balance' => $orderCalculatorResponse['pay_by_wallet_main_balance'],
            'pay_by_gateway' => $orderCalculatorResponse['pay_by_gateway'],
        ];


        [
            $pay_by_gift_balance,
            $pay_by_main_balance,
            $pay_by_gateway
        ] = Invoice::pay_calculator($validatorResponse['total_invoices_amount_differences'],$this->pay_type,$this->parentOrder->customer,$this->parentOrder->pay_by_wallet_gift_balance);

        $validatorResponse['pay_by_wallet_gift_balance'] = $pay_by_gift_balance;
        $validatorResponse['pay_by_wallet_main_balance'] = $pay_by_main_balance;
        $validatorResponse['pay_by_gateway'] = $pay_by_gateway;

        // check wallet balance
        if ($this->pay_type == 'wallet' &&
            $validatorResponse['total_invoices_amount_differences'] > 0 &&
            $this->parentOrder->customer->balance < $validatorResponse['total_invoices_amount_differences'] &&
            $this->wantError)
        {
            throw Helpers::makeValidationException('موجودی کیف پول کافی نیست');
        }
        // ====================================================================================
        $this->validatorResponse = $validatorResponse;
        return $validatorResponse;
    }
    public function applier($addCarts = null, $deleteCarts = null, $newAddress = null, $newShipping = null)
    {
        $validatorResponse = $this->validatorResponse;
        $serviceResponse = [
            'has_process_completed' => false,
            'redirect_to_gateway' => false,
            'order' => $this->parentOrder
        ];
        // if invoice_amount=0 customer needs to pay nothing. so we complete the process and call success method
        if ($validatorResponse['total_invoices_amount_differences'] == 0) {
            $this->onSuccess($addCarts, $deleteCarts, $newAddress, $newShipping);
            $serviceResponse['has_process_completed'] = true;
            return $serviceResponse;
        }

        if ($validatorResponse['pay_by_gateway'] > 0)
        { // so the customer needs to pay something. now we create a link for this update, or we redirect to the gateway with creating of OrderUpdater method.
            switch ($this->pay_type)
            {
                case 'wallet':
                    /* when the process arrives here it means that we have checked in the validator. so the wallet has balance. therefore there exists enough balance in wallet  */
                    $invoice = $this->parentOrder->invoices()->create([
                        'amount' => $validatorResponse['total_invoices_amount_differences'],
                        /* todo: this is wrong */
                        'wallet_amount' => $validatorResponse['pay_by_wallet_main_balance'] + $validatorResponse['pay_by_wallet_gift_balance'],
                        'gift_wallet_amount' => $validatorResponse['pay_by_wallet_gift_balance'],
                        'type' => $this->pay_type,
                        'transaction_id' => null,
                        'status' => Invoice::STATUS_SUCCESS
                    ]);
                    // we deposit or withdraw this invoice on wallet.
                    if ($validatorResponse['pay_by_wallet_gift_balance'] != 0) Payment::withDrawFromWalletGiftBalance($this->parentOrder->customer, $invoice);
                    if ($validatorResponse['pay_by_wallet_main_balance'] != 0) {
                        if ($validatorResponse['pay_by_wallet_main_balance'] > 0) { // withdraw from wallet
                            $transaction = $this->parentOrder->customer->withdraw($invoice->wallet_amount, [
                                'description' => "کاهش از کیف پول در اثر ویرایش سفارش با شناسه " . $this->parentOrder->id
                            ]);
                        } else {
                            $depositAmount = ($invoice->wallet_amount) * -1; // deposit amount must be positive for Bavix Package
                            $transaction = $this->parentOrder->customer->deposit($depositAmount, [
                                'description' => "برگشت مبلغ سفارش در اثر ویرایش سفارش با شناسه" . $this->parentOrder->id
                            ]);
                        }
                        $invoice->update(['transaction_id' => $transaction->id]);
                    }
                    $this->onSuccess($addCarts, $deleteCarts, $newAddress, $newShipping);
                    $serviceResponse['has_process_completed'] = true;
                    break;
                case 'gateway': /* =========================================================== */
                case 'both': /* =========================================================== */
                    $newOrderUpdater = OrderUpdater::store(
                        payable_amount:  $validatorResponse['pay_by_gateway'],
                        customer_id:  $this->parentOrder->customer_id,
                        order_id:  $this->parentOrder->id,
                        update_type: '',
                        update_items:  json_encode([
                            'addCarts' => ($addCarts) ? OrderUpdater::CartsSummarizer($addCarts) : [],
                            'deleteCarts' => ($deleteCarts) ? OrderUpdater::CartsSummarizer($deleteCarts) : [],
                            'newShipping_id' => $newShipping->id ?? null,
                            'newAddress_id' => $newAddress->id ?? null,
                        ])
                    );
                    if ($this->payment_driver) {
                        $serviceResponse['redirect_to_gateway'] = true;
                        $serviceResponse['newOrderUpdater'] = $newOrderUpdater;
                    } else $serviceResponse['newOrderUpdaterLink'] = $newOrderUpdater->link_generator();
                    break;
            }
        } else {
            // $validatorResponse['pay_by_gateway'] <= 0 ... we should reduce wallet
            $invoice = $this->parentOrder->invoices()->create([
                'amount' => $validatorResponse['total_invoices_amount_differences'],
                'wallet_amount' => $validatorResponse['pay_by_wallet_main_balance'] + $validatorResponse['pay_by_wallet_gift_balance'],
                'gift_wallet_amount' => $validatorResponse['pay_by_wallet_gift_balance'],
                'type' => $this->pay_type,
                'transaction_id' => null,
                'status' => Invoice::STATUS_SUCCESS
            ]);
            if ($validatorResponse['pay_by_wallet_gift_balance'] != 0) Payment::withDrawFromWalletGiftBalance($this->parentOrder->customer, $invoice);
            if ($validatorResponse['pay_by_wallet_main_balance'] != 0) {
                if ($validatorResponse['pay_by_wallet_main_balance'] > 0) {
                    $transaction = $this->parentOrder->customer->withdraw($invoice->wallet_amount, [
                        'description' => "کاهش از کیف پول در اثر افزودن آیتم به سفارش"
                    ]);
                } else {
                    $depositAmount = ($invoice->wallet_amount) * -1; // deposit amount must be positive for Bavix Package
                    $transaction = $this->parentOrder->customer->deposit($depositAmount, [
                        'description' => "برگشت مبلغ سفارش در اثر ویرایش سفارش"
                    ]);
                }
                $invoice->update(['transaction_id' => $transaction->id]);
            }
            $this->onSuccess();
            $serviceResponse['has_process_completed'] = true;
        }
        return $serviceResponse;
    }
    private function onSuccess($addCarts = null, $deleteCarts = null, $newAddress = null, $newShipping = null)
    {
        $validatorResponse = $this->validatorResponse;
        // store allOrderItems ======================================
        foreach ($this->allOrderItems as $item) {
            $item->save();
        }
        // update shipping_amount, discount_amount =======================
        $this->parentOrder->fill([
            'discount_on_order' => $validatorResponse['discount_on_order'],
            'discount_on_coupon' => $validatorResponse['discount_on_coupon'],
            'discount_on_items' => $validatorResponse['discount_on_items'],

            'shipping_amount' => $validatorResponse['shipping_amount'],
            'shipping_packet_amount' => $validatorResponse['shipping_packet_amount'],
            'shipping_first_packet_size' => $validatorResponse['shipping_first_packet_size'],
            'shipping_more_packet_price' => $validatorResponse['shipping_more_packet_price'],
            'weight' => $validatorResponse['weight'],

            'total_items_amount' => $validatorResponse['total_items_amount'],
            'total_quantity' => $validatorResponse['total_quantity'],
            'total_items_count' => $validatorResponse['total_items_count'],
            'total_invoices_amount' => $validatorResponse['total_invoices_amount'],

            'pay_by_wallet_gift_balance' => $validatorResponse['pay_by_wallet_gift_balance'],
            'pay_by_wallet_main_balance' => $validatorResponse['pay_by_wallet_main_balance'],
        ]);
        // update for newShipping =================
        if ($newShipping)
            $this->parentOrder->fill(['shipping_id' => $newShipping->id]);
        // update for newAddress =================
        if ($newAddress) {
            $this->parentOrder->fill([
                'address_id' => $newAddress->id,
                'address' => $newAddress->toJson(),
            ]);
        }
        $this->parentOrder->save();
        // change store balance for add mode ==========================================
        foreach ($addCarts ?? [] as $cart) {
            (new StoreBalanceService($cart->variety_id))->getFromStore($cart->quantity, "برداشت از انبار بابت ویرایش سفارش با شناسه " . $this->parentOrder->id);
        }
        // change store balance for delete mode ==========================================
        foreach ($deleteCarts ?? [] as $cart) {
            (new StoreBalanceService($cart->variety_id))->sendToStore($cart->quantity, "بازگشت به انبار بابت ویرایش سفارش با شناسه " . $this->parentOrder->id);
        }
//        Order::updateTotalFields($this->parentOrder->id);
    }

// =====================================================================================================================
// =====================================================================================================================
    private function general_order_validations_for_update_order()
    {
        // check order status should be new. of course, if order be children its status is not important
        if ($this->order->parent_id) {
            if ($this->order->parent->status != Order::STATUS_NEW)
                throw Helpers::makeValidationException('فقط سفارش هایی با وضعیت در انتظار تکمیل قابل ویرایش هستند');
        } elseif ($this->order->status != Order::STATUS_NEW)
                throw Helpers::makeValidationException('فقط سفارش هایی با وضعیت در انتظار تکمیل قابل ویرایش هستند');
        // there exists a limitation for orderUpdater. each order just can have one orderUpdater
        elseif ($this->parentOrder->customer->orderUpdaters()->where('order_id', $this->parentOrder->id)->payable()->count() != 0)
            throw Helpers::makeValidationException('فاکتور پرداخت نشده وجود دارد');
    }
}
