<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Order\Entities\Order;
//use Shetabit\Shopit\Modules\Order\Entities\OrderStatusLog as BaseOrderStatusLog;

//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\HasFilters;
use Modules\Core\Traits\HasMorphAuthors;

class OrderStatusLog extends Model
{
    use /*HasCommonRelations, */HasMorphAuthors, HasFilters;

    protected $fillable = [
        'status',
        'transferd'
    ];

    //Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function store($order, $status)
    {
        $log = new static();
        $log->fill(compact('status'));
        $log->order()->associate($order);
        $log->save();
    }
}
