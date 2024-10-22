<?php

namespace Modules\Order\Entities;

//use Shetabit\Shopit\Modules\Order\Entities\OrderItemLog as BaseOrderItemLog;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasAuthors;
//use Modules\Core\Entities\BaseModel;

class OrderItemLog extends Model
{
    use HasAuthors;

    protected $fillable = [
        'type',
        'quantity'
    ];

    const TYPE_INCREMENT = 'increment';
    const TYPE_DECREMENT = 'decrement';
    const TYPE_NEW = 'new';
    const TYPE_DELETE = 'delete';

    public static function booted()
    {
        static::deleting(function (\Modules\Order\Entities\OrderItemLog $orderItemLog) {
            $orderItemLog->orderLog()->delete();
        });
    }


    public static function getAvailableTypes()
    {
        return [self::TYPE_DECREMENT, self::TYPE_DELETE, self::TYPE_INCREMENT,self::TYPE_NEW];
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function orderLog()
    {
        return $this->belongsTo(OrderLog::class);
    }

    public static function addLog($orderLog, $orderItem, $type, $quantity)
    {
        /** @var static $log */
        $log = new static([
            'type' => $type,
            'quantity' => $quantity
        ]);

        $log->orderItem()->associate($orderItem);
        $log->orderLog()->associate($orderLog);
        $log->save();

        return $log;
    }
}
