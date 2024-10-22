<?php

namespace Modules\Order\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Customer\Entities\Customer;
//use Shetabit\Shopit\Modules\Order\Http\Requests\Admin\OrderStoreRequest as BaseOrderStoreRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Indicates whether validation should stop after the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'bail|required|integer|min:1|exists:customers,id',
            'address_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    return $query->where('customer_id', $this->customer_id);
                })
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
            'discount_amount' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:65000',
            'varieties' => 'required|array',
            'varieties.*.id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('varieties', 'id')
            ],
            'varieties.*.quantity' => ['required', 'integer', 'min:1'],
            'reserved' => 'nullable|boolean'
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
        $this->merge(['delivered_at' => now()]);
        $this->customer = Customer::query()->findOrFail($this->customer_id);
        $service = new \Modules\Order\Services\Validations\Customer\OrderValidationService(
            request: $this->all(),
            customer: $this->customer,
            byAdmin: true
        );
        $this->orderStoreProperties = $service->properties;
    }

}
