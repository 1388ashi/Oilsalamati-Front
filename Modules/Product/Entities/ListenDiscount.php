<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Customer\Entities\Customer;
use Modules\Product\Notifications\listenDiscountNotification;

class ListenDiscount extends Model
{
    use HasDefaultFields;

    protected $defaults = [
        'total_sent' => 0
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function store(Customer $customer, Product $product)
    {
        if ($listenDiscount = static::where('customer_id', $customer->id)
            ->where('product_id', $product->id)->first()
        ) {
            return $listenDiscount;
        }

        $listenDiscount = new static();
        $listenDiscount->customer()->associate($customer);
        $listenDiscount->product()->associate($product);
        $listenDiscount->save();

        return $listenDiscount;
    }

    public static function send($product)
    {
        $listens = static::query()->where('product_id', $product->id)->get();
        foreach ($listens as $listen){// TODO یکدفع تو ی رکورد ۱۰۰۰ تا یوزر بره تو جاب
            $listen->customer->notify(new listenDiscountNotification($product));
        }
    }

}
