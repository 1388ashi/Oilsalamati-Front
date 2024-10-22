<?php

namespace Modules\Order\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
//use Shetabit\Shopit\Modules\Order\Http\Requests\Admin\AddItemsRequest as BaseAddItemsRequest;

class AddItemsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' =>  'required|integer|exists:products,id',
            'variety_id' =>  'required|integer|exists:varieties,id',
            'quantity' =>  'required|integer|min:1',
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
        $product = Product::query()->findOrFail($this->product_id);
        $variety = $product->varieties()->with(['product', 'attributes', 'color'])->where('id', $this->variety_id)->first();
        $this->variety = $variety;
        $order = Order::query()->findOrFail($orderId);
        $varietyExists = $order->items()->where('variety_id', $this->variety_id)->exists();
        if (is_null($variety)){
            throw Helpers::makeValidationException("این تنوع متعلق به این محصول نمی باشد.");
        }
        if ($varietyExists){
            throw Helpers::makeValidationException("این تنوع در لیست سفارش  کاربر موجود است");
        }
        if ($product->status != Product::STATUS_AVAILABLE){
            throw Helpers::makeValidationException("وضعیت محصول تایید شده نیست");
        }
        if ($variety->quantity < $this->quantity){
            throw Helpers::makeValidationException("تعداد سفارش این تنوع بیشتر از موجودی است. موجودی این تنوع : {$variety->quantity}");
        }
        if ($order->status == Order::STATUS_CANCELED || $order->status == Order::STATUS_FAILED){
            throw Helpers::makeValidationException("زمانی که وضعیت محصول کنسل یا خطا است نمیتوانید به آن آیتم اضافه کنید");
        }
    }

}
