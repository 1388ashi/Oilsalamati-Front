<?php

namespace Modules\Order\Entities;

//use \Shetabit\Shopit\Modules\Order\Entities\MiniOrder as BaseMiniOrder;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasMorphAuthors;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\MiniOrderItem;
use Modules\Store\Entities\Store;
use Modules\Core\Classes\Transaction;
use Modules\Core\Helpers\Helpers;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

// برای فروش حضوری
// سفارش بدون مشتری, آدرس و ..
class MiniOrder extends Model
{
    use HasMorphAuthors/*, LogsActivity*/;

    public $with = ['miniOrderItems', 'customer', 'creator'];

    protected $fillable = ['description', 'discount_amount', 'tracking_code', 'customer_id',
        'status', 'from_wallet_amount'];

    const TYPE_SELL = 'sell';
    const TYPE_REFUND = 'refund';
    const TYPE_BOTH = 'both';

//    public function getActivitylogOptions(): LogOptions
//    {
//        $user = auth()->user();
//
//        return LogOptions::defaults()
//            ->useLogName('MiniOrder')
//            ->logAll()
//            ->logOnlyDirty()
//            ->setDescriptionForEvent(function ($eventName) use ($user) {
//                $eventName = \Modules\Core\Helpers\Helpers::setEventNameForLog($eventName);
//                return "سفارش حضوری با شناسه {$this->id} توسط '{$user->username}' {$eventName} شد.";
//            });
//    }

    public static function booted()
    {
        static::deleting(function ($miniOrder) {
            $miniOrder->miniOrderItems->each(function ($miniOrderItem) {
                $variety = $miniOrderItem->variety;
                if ($variety) {
                    $variety->load('attributes');
                    if ($miniOrderItem->type == MiniOrderItem::TYPE_REFUND) {
                        if ($variety->store->balance < $miniOrderItem->quantity) {
                            throw Helpers::makeValidationException('به علت نبود موجودی محصول با شناسه '
                                . $miniOrderItem->product->id . ' امکان حذف این سفارش وجود ندارد');
                        }
                        Store::insertModel((object)[
                            'type' => Store::TYPE_DECREMENT,
                            'description' => "از محصول {$variety->title} به علت پاک کردن سفارش حضوری کم شد ",
                            'quantity' => $miniOrderItem->quantity,
                            'variety_id' => $variety->id
                        ]);
                    } else {
                        Store::insertModel((object)[
                            'type' => Store::TYPE_INCREMENT,
                            'description' => "از محصول {$variety->title} به علت پاک کردن سفارش حضوری اضافه شد ",
                            'quantity' => $miniOrderItem->quantity,
                            'variety_id' => $variety->id
                        ]);
                    }

                }
                $miniOrderItem->delete();
            });
        });
    }

    public static function getAvailableTypes()
    {
        return [self::TYPE_SELL, self::TYPE_REFUND, self::TYPE_BOTH];
    }

    public function getTotalAttribute()
    {
        $total = 0;
        foreach ($this->miniOrderItems as $minOrderItem) {
            $sub = $minOrderItem->type === MiniOrderItem::TYPE_SELL ? $minOrderItem->amount : -$minOrderItem->amount;
            $total += $sub * $minOrderItem->quantity;
        }

        return $total - $this->discount_amount;
    }

    public function miniOrderItems()
    {
        return $this->hasMany(MiniOrderItem::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function getAvailableStatuses()
    {
        return [self::TYPE_SELL, self::TYPE_REFUND, self::TYPE_BOTH];
    }
}
