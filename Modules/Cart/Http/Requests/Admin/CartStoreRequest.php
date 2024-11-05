<?php

namespace Modules\Cart\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Cart\Entities\Cart;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Entities\Variety;
use Shetabit\Shopit\Modules\Cart\Http\Requests\Admin\CartStoreRequest as BaseCartStoreRequest;

class CartStoreRequest extends FormRequest
{
    public Variety $variety;
    // اگر توی سبدش از قبل باشه مقدار میگیره
    public ?Cart $alreadyInCart;

    public function rules()
    {
        return [
            'quantity' => 'required|integer|min:1',
        ];
    }

    protected function passedValidation()
    {
        $varietyId = Helpers::getModelIdOnPut('variety');
        $this->variety = Variety::query()->with('product.activeFlash')->whereKey($this->variety_id)->firstOrFail();
        if ($this->variety->quantity == null || $this->variety->quantity == 0){
            throw Helpers::makeValidationException('تنوع مورد نظر ناموجود است');
        }
        $user = Auth::guard('customer')->user();  
        if (!$user) {  
            throw new \Exception('User not authenticated.');
        }  
        $this->alreadyInCart = $user->carts()->where('variety_id', $this->variety->id)->first(); 
        if ($this->quantity + ($this->alreadyInCart ? $this->alreadyInCart->quantity : 0) > $this->variety->quantity){
            throw Helpers::makeValidationException(' از تنوع مورد نظر فقط ' . $this->variety->quantity . " {$this->variety->product->unit->symbol} " . "موجود است ");
        }
    }
}
