<?php

namespace Modules\Product\Entities;

//use Shetabit\Shopit\Modules\Product\Entities\ListenCharge as BaseListenCharge;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Modules\Product\Notifications\SendListenChargeNotification as ListenChargeNotification;

class ListenCharge extends Model
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
        if ($listenCharge = static::where('customer_id', $customer->id)
            ->where('product_id', $product->id)->first()
        ) {
            return $listenCharge;
        }

        $listenCharge = new static();
        $listenCharge->customer()->associate($customer);
        $listenCharge->product()->associate($product);
        $listenCharge->save();

        return $listenCharge;
    }

    public static function send($product)
    {
        $listens = static::query()->where('product_id', $product->id)->get();
        foreach ($listens as $listen){// TODO یکدفع تو ی رکورد ۱۰۰۰ تا یوزر بره تو جاب
            $listen->customer->notify(new ListenChargeNotification($product));
        }
    }
}
