<?php

namespace Modules\Order\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Order\Http\Requests\Admin\OrderUpdateRequest as BaseOrderUpdateRequest;

use Faker\Extension\Helper;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderItem;
use Modules\Order\Services\Validations\Admin\OrderValidationService;
use Modules\Shipping\Entities\Shipping;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('addresses', 'id')
            ],
            'shipping_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('shippings', 'id')->where(function ($query) {
                    return $query->where('status', 1);
                })
            ],
            'discount_amount' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:65000',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function passedValidation()
    {
        $orderId = Helpers::getModelIdOnPut('order');
        /** @var Order $order */
        $order = Order::query()->findOrFail($orderId);
        $parentOrder = $order->reserved_id == null ? $order : Order::findOrFail($order->reserved_id);
        $totalQuantity = $parentOrder->getTotalTotalQuantity();
        /** @var Customer $customer */
        $customer = $order->customer;
        /** @var OrderItem $items */

        $totalAmount = $order->getTotalAmount();

        if ($this->discount_amount > $totalAmount){
            throw Helpers::makeValidationException('تخفیف نمیتواند از قیمت مجموع فاکتور بیشتر باشد');
        }
        /** @var Address $address */
        $address = $customer->addresses()->whereKey($this->address_id)->first();
        if (!$address){
            throw Helpers::makeValidationException('آدرس انتخاب شده برای این مشتری نمی باشد.');
        }
        /** @var Shipping $shipping */
        $shipping = Shipping::query()->whereKey($this->shipping_id)->first();
        $check = $shipping->checkShippableAddress($address->city);
        if (!$check){
            throw Helpers::makeValidationException('شیوه ارسالی برای این آدرس تعریف نشده است.');
        }

        $this->customer = $customer;
        $this->order = $order;
        $this->address = $address;

    }
}
