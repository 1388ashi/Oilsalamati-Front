<?php

namespace Modules\Cart\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Cart\Http\Requests\Admin\CartUpdateRequest as BaseCartUpdateRequest;

class CartUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'quantity' => 'required|integer|min:0'
        ];
    }

    protected function passedValidation()
    {
        $cartId = $this->id;
        $cart = Cart::query()->where('id', $cartId)->owner()->firstOrFail();
        if ($cart->variety->quantity == null || $cart->variety->quantity == 0){
            throw Helpers::makeValidationException('تنوع مورد نظر ناموجود است');
        }
        if ($this->quantity > $cart->variety->quantity){
            throw Helpers::makeValidationException(" از تنوع مورد نظر فقط {$cart->variety->quantity} موجود است ");
        }
        $this->cart = $cart;

    }
}
