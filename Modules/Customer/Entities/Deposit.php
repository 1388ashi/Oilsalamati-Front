<?php

namespace Modules\Customer\Entities;

//use Shetabit\Shopit\Modules\Customer\Entities\Deposit as BaseDeposit;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Notifiable;
use Modules\Admin\Entities\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Classes\Transaction;
use Modules\Invoice\Classes\Payable;
use Modules\Invoice\Entities\Invoice;
use Modules\Customer\Notifications\DepositWalletFailedNotification;
use Modules\Customer\Notifications\DepositWalletSuccessfulNotification;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Modules\Customer\Entities\Customer;

class Deposit extends Payable
{
    use Notifiable/*, LogsActivity*/;

    const STATUS_NEW = 'new';
    const STATUS_WAIT_FOR_PAYMENT = 'wait_for_payment';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUCCESS = 'success';
    const STATUS_CANCELED = 'canceled';

    protected static $recordEvents = ['created'];

    protected $fillable = [
        'amount',
        'status'
    ];

//    public function getActivitylogOptions(): LogOptions
//    {
//        $user = \Auth::user();
//        if($user instanceof Admin) {
//            $message = "ادمین با شناسه {$user->id} کیف پول کاربر با شناسه {$this->id} را شارژ کرد";
//        }else{
//            $message = "کاربر با شماره موبایل {$this->mobile} کیف پول خود را شارژکرد";
//        }
//        return LogOptions::defaults()
//            ->useLogName('Wallet')
//            ->logAll()
//            ->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($message){
//                return $message;
//            });
//    }

    public static function getAvailableStatus(): array
    {
        return [static::STATUS_NEW, static::STATUS_WAIT_FOR_PAYMENT, static::STATUS_IN_PROGRESS,
            static::STATUS_SUCCESS, static::STATUS_CANCELED];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public static function storeModel($amount)
    {
        $user = \Auth::user();
        $deposit = new static;

        $deposit->fill([
            'amount' =>  $amount,
            'status' => static::STATUS_WAIT_FOR_PAYMENT
        ]);
        $deposit->customer()->associate($user->id);
        $deposit->save();

        return $deposit;
    }

    public function isPayable(): bool
    {
        return $this->status === static::STATUS_WAIT_FOR_PAYMENT;
    }

    public function getPayableAmount(): int
    {
        return (int)$this->amount;
    }

    public function onSuccessPayment(Invoice $invoice): View|Factory|JsonResponse|Application
    {
        $customer = $invoice->payable->customer()->first();
        $transaction = $customer->deposit($invoice->amount);
        $this->transaction()->associate($transaction);
        $this->status = static::STATUS_SUCCESS;
        $this->save();
        $type = 'wallet';
        $this->notify(new DepositWalletSuccessfulNotification($this->customer, $invoice->amount));

        return view('core::invoice.callback', ['invoice' => $invoice, 'type' => $type]);
    }

    public function onFailedPayment(Invoice $invoice): View|Factory|JsonResponse|Application
    {
        $this->status = static::STATUS_CANCELED;
        $this->save();

        $type = 'wallet';
        $this->notify(new DepositWalletFailedNotification($this->customer, $invoice->amount));

        return view('core::invoice.callback', ['invoice' => $invoice, 'type' => $type]);
    }
}

