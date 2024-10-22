<?php

namespace Modules\Order\Services\Validations\Admin;

//use Shetabit\Shopit\Modules\Order\Services\Validations\Admin\OrderValidationService as BaseOrderValidationService;

use Modules\Core\Helpers\Helpers;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Shipping\Entities\Shipping;
use Modules\Customer\Entities\Customer;

class OrderValidationService
{
    protected array $varieties = [];

    public array $varietyModels = [];

    protected array $request;

    protected int|null $orderId;

    public function __construct(array $request, int $orderId = null)
    {
        $this->request = $request;
        $this->varieties = $this->request['varieties'];
        $this->orderId = $orderId;
        $this->checkVarieties();
    }

    public function checkVarieties()
    {
        $varietyArray = $this->varieties ?: false;

        if ($varietyArray) {
            $varietyIds = collect($varietyArray)->pluck('id');
            $varietyModels = variety::find($varietyIds);

            if (count($varietyArray) != $varietyModels->count()) {
                throw Helpers::makeValidationException('تعداد مشخصات وارد شده نامعتبر است');
            }

            $sumVarietiesPrice = 0;
            foreach ($varietyArray as $key => $varietyFromRequest) {
                $currentVarietyModel = $varietyModels[$key];
                $this->checkMaxQuantityvariety($currentVarietyModel, $varietyFromRequest);
                $this->checkAvailableVariety($currentVarietyModel);
                $sumVarietiesPrice += ($currentVarietyModel->final_price['amount'] * $varietyFromRequest['quantity']);
            }

            $this->checkCustomer($varietyModels, $sumVarietiesPrice);
        }
    }

    public function checkMaxQuantityVariety($currentVarietyModel, $varietyFromRequest)
    {
        if ($currentVarietyModel->store->balance < $varietyFromRequest['quantity']) {
            throw Helpers::makeValidationException(
                'تعداد تنوع محصول ' . $currentVarietyModel->product->title . ' باید کمتر یا مساوی تعداد کل موجود در انبار باشد. ',
                'variety_quantity'
            );
        }
    }

    public function checkAvailableVariety($currentVarietyModel)
    {
        if ($currentVarietyModel->product->status !== Product::STATUS_AVAILABLE) {
            throw Helpers::makeValidationException(
                'تنوع محصول ' . $currentVarietyModel->product->title . ' باید وضعیت آن موجود باشد. ',
                'status'
            );
        }
    }

    public function checkCustomer($varietyModels, $sumVarietiesPrice)
    {
        $order = Order::find($this->orderId);
        if ($order && $order->status !== Order::STATUS_IN_PROGRESS) {
            throw Helpers::makeValidationException('وضعیت سفارش باید در حال پردازش باشد!');
        }
        $customer = $order ? $order->customer : Customer::find($this->request['customer_id']);
        $address = $customer->addresses->where('id', $this->request['address_id'])->first();
        if ($order & $address->customer_id !== $order->customer_id) {
            throw Helpers::makeValidationException('آدرس انتخاب شده نامعتبر است!', 'address_id');
        }

        //Shipping
        $shipping = Shipping::find($this->request['shipping_id']);
        if (! $shipping->checkShippableAddress($address->city)) {
            throw Helpers::makeValidationException('شیوه ارسال انتخاب شده نامعتبر است.', 'shipping_id');
        }
        $shippingAmount = $shipping->getPrice($address->city, $sumVarietiesPrice);

        $orderTotalAmount = ($sumVarietiesPrice - $this->request['discount_amount']) + $shippingAmount;

        //Check balance
        $balance = $customer->balance;
        //condition1: ($order && $balance < ($orderTotalAmount - $order->getTotalAmount())) => for update order
        //condition2: $balance < $orderTotalAmount => for store order
        if (($order && $balance < ($orderTotalAmount - $order->getTotalAmount()))
            or $balance < $orderTotalAmount
        ) {
            throw Helpers::makeValidationException('مجموع مبلغ سفارش بیشتر از موجودی کیف پول مشتری است', 'customer_wallet');
        }

        request()->merge(compact(
            'customer',
            'shipping',
            'shippingAmount',
            'address',
            'orderTotalAmount',
            'varietyModels',
            'order'
        ));
    }

}
