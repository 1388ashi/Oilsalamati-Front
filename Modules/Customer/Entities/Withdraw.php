<?php

namespace Modules\Customer\Entities;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Support\Facades\Log;
use Modules\Core\Classes\Transaction;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
//use Modules\Core\Helpers\Helpers;
//use Modules\Customer\Entities\Customer;
use Modules\Customer\Http\Requests\Customer\WithdrawStoreRequest;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;

class Withdraw extends Model implements Product
{
    use /*HasCommonRelations,*/ HasWallet/*, LogsActivity*/;

//    public static $commonRelations = [
//        'transactions', 'customer'
//    ];

    protected $fillable = [
        'amount',
        'card_number',
        'bank_account_number',
        'shaba_code',
        'tracking_code'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELED = 'canceled';

    public static function getStatusTranslation($status)
    {
        $trans = [
            static::STATUS_PENDING => 'در انتظار تکمیل',
            static::STATUS_PAID => 'پرداخت شده',
            static::STATUS_CANCELED => 'لغو شده'
        ];

        return $trans[$status] ?? 'نامعلوم';
    }

    public static function booted()
    {
        static::updating(function (\Modules\Customer\Entities\Withdraw $withdraw) {
            if ($withdraw->isDirty('status')) {
                $oldStatus = $withdraw->getOriginal('status');
                $newStatus = $withdraw->status;
                if (static::isSuccessStatus($oldStatus) && static::isFailedStatus($newStatus)) {
                    /** @var Customer $customer */
                    $customer = $withdraw->customer;
                    $customer->refund($withdraw);
                } else if (static::isFailedStatus($oldStatus) && static::isSuccessStatus($newStatus)) {
                    /** @var Customer $customer */
                    $customer = $withdraw->customer;
                    $customer->pay($withdraw);
                }
            }
        });
    }

    public static function getAvailableStatuses()
    {
        return [
            static::STATUS_PENDING, static::STATUS_PAID,
            static::STATUS_CANCELED
        ];
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        if (app()->runningInConsole()){
//            return LogOptions::defaults()->submitEmptyLogs();
//        }
//        $admin = \Auth::user();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Withdraw')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "دسته بندی {$this->title} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isCancelableByCustomer()
    {
        return !in_array($this->status, [
            static::STATUS_PAID,
            static::STATUS_CANCELED
        ]);
    }

    public static function isSuccessStatus($status): bool
    {
        return in_array($status, [
            static::STATUS_PENDING, static::STATUS_PAID
        ]);
    }

    public static function isFailedStatus($status): bool
    {
        return in_array($status, [
            static::STATUS_CANCELED
        ]);
    }

    public static function store(Customer $customer, WithdrawStoreRequest $request)
    {
        // all() method is overridden at WithdrawStoreRequest
        $withdraw = new static($request->all());
        /** @var Transaction $transaction */
        $withdraw->status = static::STATUS_PENDING;
        $withdraw->customer()->associate($customer);
        $withdraw->save();
        $customer->pay($withdraw);

        return $withdraw;
    }

    public function canBuy(\Bavix\Wallet\Interfaces\Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        return true;
    }

    public function getAmountProduct(\Bavix\Wallet\Interfaces\Customer $customer)
    {
        return $this->amount;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'description' => 'برداشت از کیف پول با شناسه #' . $this->id . ' با وضعیت ' . static::getStatusTranslation($this->status)
        ];
    }

    public function scopeFilters($query)
    {
        $customerId = request('customer_id');
        $status = request('status');
        $startDate = request('start_date');
        $endDate = request('end_date');

        return $query  
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate));
    }
}
