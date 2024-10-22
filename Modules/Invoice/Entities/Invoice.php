<?php

namespace Modules\Invoice\Entities;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
//use Modules\Admin\Entities\Admin;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
//use Modules\Core\Helpers\Helpers;
use Modules\Invoice\Classes\Payable;
use Modules\Order\Entities\Order;
//use Shetabit\Shopit\Modules\Invoice\Entities\Invoice as BaseInvoice;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;
/**
 * Class Invoice
 * @package Modules\Bonusme\Entities
 * @property Payable $payable
 * @see Payment
 */
class Invoice extends Model implements Product
{

    protected $fillable = [
        'amount',
        'type',
        'transaction_id',
        'status',
        'status_detail',
        'wallet_amount',
        'gift_wallet_amount',
        'has_reduced_gift_wallet'
    ];

    public static function storeByWallet(Payable $payable, Transfer $transfer = null, $status = 'success'): \Shetabit\Shopit\Modules\Invoice\Entities\Invoice
    {
        $walletPayableAmount = static::getWalletPayableAmount($payable);
        $amount = $payable->getPayableAmount();

        $invoice = new static([
            'amount' => $amount,
            'wallet_amount' => $walletPayableAmount,
            'gift_wallet_amount' => self::getGiftBalanceWalletAmount($walletPayableAmount,$payable),
            'type' => static::getType($amount,$walletPayableAmount),
            'transaction_id' => $transfer?->id,
            'status' => $status
        ]);

        $invoice->payable()->associate($payable);
        $invoice->save();

        if ($invoice->gift_wallet_amount){
            $order = Order::query()->where('id',$invoice->payable_id)->first();
            if ($order){
                $order->gift_wallet_amount = $invoice->gift_wallet_amount;
                $order->save();
            }
        }

        return $invoice;
    }

    //مقدار خرید انجام شده توسط هدیه
    public static function getGiftBalanceWalletAmount($walletPayableAmount,$payable)
    {
        $gift_balance = DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $payable->customer_id)
            ->latest('id')
            ->first()->gift_balance;

        $gb=0;

        if ($walletPayableAmount >= $gift_balance){
            $gb= $gift_balance;
        }elseif($walletPayableAmount < $gift_balance){
            $gb= $walletPayableAmount;
        }

        return $gb;
    }


    public function invoiceLogs()
    {
        return $this->hasMany(InvoiceLog::class);
    }






    // came from vendor ================================================================================================
    use /*HasCommonRelations,*/ HasWallet/*, LogsActivity*/;

//    protected static $commonRelations = [
//        /*'payable'*/
//    ];

    protected $attributes = [
        'wallet_amount' => 0
    ];

    const PAY_TYPE_WALLET = 'wallet';
    const PAY_TYPE_GATEWAY = 'gateway';
    const PAY_TYPE_BOTH = 'both';

    const STATUS_PENDING = 'pending';
    const STATUS_FAILED = 'failed';
    const STATUS_SUCCESS = 'success';

    public static function booted()
    {
        static::creating(function ($invoice) {
            if ($invoice->wallet_amount === 0) {
                $invoice->type = static::PAY_TYPE_GATEWAY;
            }
        });
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        $admin = \Auth::user() ?? Admin::query()->first();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Invoice')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name, $admin){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                $causer = $admin instanceof Admin ? 'ادمین' : 'مشتری';
//
//                return "فاکتور با شناسه {$this->id} توسط {$causer} {$name} {$eventName} شد";
//            });
//    }

    public static function getAvailablePayType(): array
    {
        return [static::PAY_TYPE_BOTH, static::PAY_TYPE_GATEWAY, static::PAY_TYPE_WALLET];
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isExpired()
    {
        return false;
    }

    public function scopePendingPaid($query)
    {
        $query->where('status', static::STATUS_PENDING);
    }

    public function getPayAmount()
    {
        return (int)$this->amount - $this->wallet_amount;
    }

    public static function getType($amount, $walletPayableAmount): string
    {
        if($amount == $walletPayableAmount){
            $type = static::PAY_TYPE_WALLET;
        }elseif ($walletPayableAmount == 0){
            $type = static::PAY_TYPE_GATEWAY;
        }else{
            $type = static::PAY_TYPE_BOTH;
        }

        return $type;
    }

    public static function getWalletPayableAmount($payable)
    {
        /** @var Order $payable */
        $balance = $payable->customer->balance;
        if ($balance == 0){
            return 0;
        }

        if ($balance >= $payable->getPayableAmount()) {
            $payWalletAmount = $payable->getPayableAmount();
        }else{
            $payWalletAmount = $balance;
        }

        return $payWalletAmount;
    }

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = null): bool
    {
        return !$customer->paid($this);
    }

    public function getAmountProduct(Customer $customer)
    {
        return $this->getTotalAmount();
    }

    public function getMetaProduct(): ?array
    {
        return [
            'customer_name' => $this->payable->customer->full_name,
            'customer_mobile' => $this->payable->customer->mobile,
            'description' => $this->payable->payDescription ?: 'خرید سفارش به شماره #' . $this->payable?->id
        ];
    }

    public function getUniqueId(): string
    {
        return (string) $this->getKey();
    }

    public function getTotalAmount()
    {
        $totalItemsAmount = $this->payable->activeItems
            ->reduce(function ($total, $item) {
                return $total + ($item->amount * $item->quantity);
            });
        if (!is_null($this->payable->reserved_id)){
            $amount = ($totalItemsAmount) - $this->payable->attributes['discount_amount'];
        }else{
            $amount = ($totalItemsAmount + $this->payable->attributes['shipping_amount']) - $this->payable->attributes['discount_amount'];
        }

        return $amount;
    }


    public static function pay_calculator ($invoice_amount,$pay_type,$customer,$max_used_gift):array
    {
        $pay_by_wallet_gift_balance = 0;
        $pay_by_wallet_main_balance = 0;
        $pay_by_gateway = 0;

        if ($invoice_amount > 0)
        {
            if ($pay_type == 'gateway') {
                $pay_by_gateway = $invoice_amount;
            } else {
                // we should reduce from wallet
                $wallet_gift_balance = $customer->wallet->gift_balance;
                $wallet_main_balance = $customer->wallet->balance - $wallet_gift_balance;

                if ($wallet_gift_balance > 0) {
                    if ($wallet_gift_balance >= $invoice_amount) {
                        $pay_by_wallet_gift_balance = $invoice_amount;
                        $invoice_amount = 0;
                    } else {
                        $pay_by_wallet_gift_balance = $wallet_gift_balance;
                        $invoice_amount -= $wallet_gift_balance;
                    }
                }
                if ($wallet_main_balance > 0) {
                    if ($wallet_main_balance >= $invoice_amount) {
                        $pay_by_wallet_main_balance = $invoice_amount;
                        $invoice_amount = 0;
                    } else {
                        $pay_by_wallet_main_balance = $wallet_main_balance;
                        $invoice_amount -= $wallet_main_balance;
                    }
                }
                if ($invoice_amount > 0)
                    $pay_by_gateway = $invoice_amount;
            }
        }
        elseif ($invoice_amount == 0)
        {
            return [$pay_by_wallet_gift_balance, $pay_by_wallet_main_balance, $pay_by_gateway];
        }
        else
        {
            // we should return to the customer's wallet. in max of used of gift balance. We deposit the rest into the wallet.
            if ($max_used_gift > 0) {
                if ($max_used_gift <= $invoice_amount) {
                    $pay_by_wallet_gift_balance = $invoice_amount;
                    $invoice_amount = 0;
                } else {
                    $pay_by_wallet_gift_balance = $max_used_gift * -1;
                    $invoice_amount += $max_used_gift;
                }
            }
            if ($invoice_amount < 0)
                $pay_by_wallet_main_balance = $invoice_amount;
        }
        return [$pay_by_wallet_gift_balance, $pay_by_wallet_main_balance, $pay_by_gateway];
    }
}
