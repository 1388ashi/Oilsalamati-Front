<?php

namespace Modules\Order\Entities;

//use Shetabit\Shopit\Modules\Order\Entities\OrderLog as BaseOrderLog;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasAuthors;
//use Modules\Order\Entities\Order;
//use Modules\Order\Entities\OrderItemLog;
use Modules\Shipping\Entities\Shipping;

class OrderLog extends Model
{
    use HasAuthors;

    protected $fillable = [
        'address',
        'shipping',
        'amount',
        'coupon',
        'status'
    ];

    protected $with = ['logItems', 'shipping'];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shipping(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shipping::class)->select(['id','name']);
    }

    public function logItems()
    {
        return $this->hasMany(OrderItemLog::class);
    }

    public static function addLog($order, $amount, $coupon, $address = null, $shipping = null, $status = null)
    {
        if (!$amount && (!$shipping) && !$address && !$coupon) {
            return;
        }
        /** @var static $log */
        $log = new static([
            'address' => $address,
            'amount' => $amount,
            'coupon' => $coupon,
            'status' => $status
        ]);
        if ($shipping){
            $log->shipping()->associate($shipping);
        }

        $log->order()->associate($order);
        $log->save();

        return $log;
    }
}
