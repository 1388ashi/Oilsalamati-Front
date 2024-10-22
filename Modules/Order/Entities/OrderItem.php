<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Admin\Entities\Admin;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\HasFilters;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasMorphAuthors;
use Modules\Flash\Entities\Flash;
use Modules\Product\Entities\Gift;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Store\Entities\Store;
//use Shetabit\Shopit\Modules\Order\Entities\OrderItem as BaseOrderItem;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderItem extends Model
{
    protected $fillable = [
        'product_id',
        'variety_id',
        'quantity',
        'amount',
        'status',
        'discount_amount',
        'extra',
        'flash_id',
        'order_id'
    ];

    public static function depositCustomer($orderItem,$customer,$amount,$newStatus)
    {
        $customer->deposit($amount, [
            'causer_id' => auth()->user()->id,
            'causer_mobile' => auth()->user()->mobile,
            'description' => "برگشت مبلغ سفارش در اثر تغییر وضعیت به {$newStatus}"
        ]);

        $orderItem->orderItemLogs()->create([
            'amount' => -$orderItem->amount,
            'status' => 'canceled'
        ]);

    }
    public function scopeFilters($query)
    {
     
    }
    public static function depositStore($orderItem)
    {
            Store::insertModel((object)
            [
                'variety_id' => $orderItem->variety_id,
                'description' => "با تغییر وضغیت سفارش با شناسه {$orderItem->order_id} به انبار اضافه شد",
                'type' => Store::TYPE_INCREMENT,
                'quantity' => $orderItem->quantity
            ]);
    }






    // came from vendor ================================================================================================
    use HasMorphAuthors, /*HasCommonRelations,*/ HasFilters/*, LogsActivity*/;

//    protected $with = ['flash', 'product', 'gifts'];

//    protected static $recordEvents = ['deleted', 'updated'];

    public static function booted()
    {
        static::deleting(function (\Modules\Order\Entities\OrderItem $orderItem) {
            $orderItem->orderItemLogs()->delete();
        });
        static::deleted(function (\Modules\Order\Entities\OrderItem $orderItem) {
            $orderItem->gifts()->detach();
        });
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        $user = auth()->user();
//        $name = $user instanceof Admin ? $user->username : $user?->mobile;
//        return LogOptions::defaults()
//            ->useLogName('OrderItems')
//            ->logAll()
//            ->logOnlyDirty()
//            ->setDescriptionForEvent(function ($eventName) use($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "آیتم سفارش با شناسه {$this->id} توسط {$name} {$eventName} شد.";
//            });
//    }

    //Relations
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variety(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }

    // اگر موقع خرید تنوع توی فلشی باشه اون رو ذخیره میکنیم
    public function flash()
    {
        return $this->belongsTo(Flash::class);
    }

    public function scopeActive($query)
    {
        $query->where('status', 1);
//        $query->where($this->getTable() . '.status', 1);
    }

    public function gifts(): belongsToMany
    {
        return $this->belongsToMany(Gift::class, 'gift_order_item')->active();
    }

    public function orderItemLogs()
    {
        return $this->hasMany(\Modules\Order\Entities\OrderItemLog::class);
    }
}
