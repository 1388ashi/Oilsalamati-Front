<?php

namespace Modules\Order\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Invoice\Entities\Payment;
use Modules\Order\Classes\OrderStoreProperties;
use Modules\Order\Services\Order\OrderCreatorService;
use Modules\Order\Services\Validations\Customer\OrderValidationService;
use Modules\Product\Entities\Variety;
use Modules\Shipping\Entities\Shipping;

//use Shetabit\Shopit\Modules\Order\Http\Requests\User\OrderStoreRequest as BaseOrderStoreRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $customer = $this->user();

        return [
            'address_id' => [
                'required',
                'exists:addresses,id',
//                Rule::exists('addresses', 'id')->where(function ($query) use ($customer) {
//                    return $query->where('customer_id', $customer->id);
//                })
            ],
            'shipping_id' => [
                'required',
                Rule::exists('shippings', 'id')->where(function ($query) {
                    return $query->where('status', 1);
                })
            ],
            'coupon_code' => [
                'nullable',
                'string',
                'max:191',
                Rule::exists('coupons', 'code')
            ],
            'discount_on_order' => 'nullable|numeric|min:0',
            'payment_driver' => ['nullable', 'string', Rule::in(Payment::getAvailableDrivers())],
            'pay_type' => ['required','string', Rule::in(['wallet','both','gateway'])],
            'reserved_at' => 'nullable|date_format:Y-m-d H:i:s|after:now'
        ];
    }


    public function passedValidation()
    {
        $customer = $this->getCustomer();
        $address = $this->getAddress($customer);
        $orderCreatorServiceObject = (new OrderCreatorService(
            carts: $this->getCarts($customer),
            customer: $customer,
            address: $address,
            shipping: Shipping::query()->active()->where('id', $this->shipping_id)->firstOrFail(),
            coupon: $this->coupon_code ? Coupon::where('code', $this->coupon_code)->firstOrFail() : null,
            discount_on_order: $this->discount_on_order ?? 0,
            pay_type: $this->pay_type,
            payment_driver: $this->getPaymentDriver(),
        ));
        $orderCreatorServiceObject->validator();

        $this->merge([
            'orderCreatorServiceObject' => $orderCreatorServiceObject,
            'customer' => $customer
        ]);


//        if ($this->has('add_item') && $this->add_item) {
//
//        } else {
//            $this->user()->removeEmptyCarts();
//            $service = new OrderValidationService(
//                request: $this->all(),
//                customer: $this->user(),
//                byAdmin: false,
//            );
//            $this->orderStoreProperties = $service->properties;
//        }



    }

// =====================================================================================================================
    private function getCustomer():Customer
    {
        if (auth()->user() instanceof Customer) {
            if (request()->header('Accept') === 'application/json') {
                return Auth::guard('customer-api')->user();
            }else {
                return Auth::guard('customer')->user();
            }
        }

        $this->validate([
            'customer_id' => 'required|integer|exists:customers,id',
        ]);
        $customer = Customer::query()->find($this->customer_id);
        if (!$customer)
            throw Helpers::makeValidationException('مشتری اشتباه است');
        return $customer;
    }
    private function getAddress(Customer $customer):Address
    {
        /* @var $address Address */
        $address = $customer->addresses()->where('id', $this->address_id)->first();
        if (!$address)
            throw Helpers::makeValidationException('آدرس اشتباه است');

        return $address;
    }

    private function getCarts(Customer $customer) {
        if (auth()->user() instanceof Customer)
            return $customer->carts;

        $fakeCarts = [];
        foreach ($this->request['varieties'] as $requestVariety) {
            $variety = Variety::findOrFail($requestVariety['id']);


            $discount_price = $variety->final_price['discount_price'];
            $price = $variety->final_price['amount'];
            if ($customer->isColleague()) {
                $discount_price = $variety->final_price['discount_colleague_price'];
                $price = $variety->final_price['colleague_amount'];
            }

            $newFakeCart = new Cart([
                'variety_id' => $requestVariety['id'],
                'quantity' => $requestVariety['quantity'],
                'discount_price' => $variety->final_price['discount_colleague_price'],
                'price' => $variety->final_price['colleague_amount'],
            ]);
            $fakeCarts[] = $newFakeCart;
        }
        $carts = collect($fakeCarts);
        return $carts;
        /* todo: we should recheck carts prices and discount_prices, of course attention to the colleague price*/
    }

    private function getPaymentDriver()
    {
        if ($this->pay_type == 'wallet') {
            return null;
        }
        $this->validate([
            'payment_driver' => 'required',
        ]);
        return $this->payment_driver;
    }

}
