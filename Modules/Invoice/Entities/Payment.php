<?php

namespace Modules\Invoice\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\DontAppend;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Customer\Entities\Customer;
use Modules\Invoice\Classes\PayDriver;
use Modules\Invoice\Events\GoingToVerifyPayment;
use Modules\Invoice\Events\PaymentVerified;
use Modules\Order\Entities\Order;
use Modules\Setting\Entities\Setting;
//use Shetabit\Shopit\Modules\Invoice\Entities\Payment as BasePayment;

class Payment extends Model
{
    public static function getAvailableDriversForFront(): array
    {
        $frontDrivers = [];
        $drivers = config('invoice.drivers');
        foreach ($drivers as $driverName => $driverInfo) {
            if (!static::isDriverEnabled($driverName)) {
                continue;
            }
            $frontDrivers[] = [
                'name' => $driverName,
                'image' => url($driverInfo['image']),
                'label' => $driverInfo['label']
            ];
        }

        usort($frontDrivers, function ($d1, $d2) {
            return static::getDriverOrder($d1['name']) - static::getDriverOrder($d2['name']);
        });

        return $frontDrivers;
    }

    public function withdrawWallet(): ?\Bavix\Wallet\Models\Transaction
    {
        /**
         * @var Invoice $invoice
         * @var Customer $customer
         */
        $invoice = $this->invoice;
        $customer = $this->invoice->payable->customer;
        /**
         * type: "withdraw",
         * wallet_id: 1,
         * uuid: "92cd1834-b33c-409f-b48a-24a8280452c7",
         * confirmed: true,
         * amount: "-1000",
         * meta: ['description' => "خرید محصول به شناسه سفارش 2"],
         * payable_id: 3,
         * payable_type: "Modules\Customer\Entities\Customer",
         * updated_at: "2021-11-14 13:20:03",
         * created_at: "2021-11-14 13:20:03",
         * id: 8,
         */

        if ($invoice->wallet_amount > 0) {
            if ($invoice->gift_wallet_amount != 0){
                Payment::withDrawFromWalletGiftBalance($customer, $invoice);
            }
            return $customer->withdraw($invoice->wallet_amount, [
                'description' => "خرید محصول به شناسه سفارش {$this->invoice->payable_id}"
            ]);
        }

        return null;
    }


    public static function withDrawFromWalletGiftBalance($customer, $invoice)
    {
        $invoice->has_reduced_gift_wallet = true;
        $invoice->save();
        /*todo: we should remove it after some days*/

        $customerWallet = $customer->wallet;
        $customerWallet->gift_balance -= $invoice->gift_wallet_amount;
        $customerWallet->save();
    }







    // came from vendor ================================================================================================
    use HasDefaultFields;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING = 'pending';

    const DRIVER_ZARINPAL = 'zarinpal';
    const DRIVER_NEXTPAY = 'nextpay';

    protected $fillable = [
        'transaction_id',
        'tracking_code',
        'gateway'
    ];

    protected $appends = ['gateway_label', 'amount'];

    protected $defaults = [
        'status' => 'pending'
    ];

    public function getAmountAttribute()
    {
        if (!$this->relationLoaded('invoice')) {
            return new DontAppend('getAmountAttribute');
        }
        return $this->invoice->amount;
    }

    public static function getAvailableDrivers(): array
    {
        return array_keys(config('invoice.drivers'));
    }

    public static function isDriverEnabled(string $name)
    {
        $settings = app(CoreSettings::class);

        return array_key_exists($name, $settings->get('invoice.active_drivers'));
    }

    public static function getDriverOrder(string $name)
    {
        $settings = app(CoreSettings::class);
        $d = $settings->get('invoice.active_drivers')[$name];

        return !isset($d['order']) ? 0 : $d['order'];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @throws \Throwable
     */
    public function verify(PayDriver $payDriver = null)
    {
        if ($this->invoice->status !== Invoice::STATUS_PENDING) {
            return view('core::invoice.exceptionError',[
                'message' => __('This invoice is already processed'),
                'description' => Setting::getFromName('transaction_message_failed')
            ]);
        }

        if ($this->invoice->isExpired()) {
            return view('core::invoice.exceptionError',[
                'message' => __('This invoice is expired'),
                'description' => Setting::getFromName('transaction_message_failed')
            ]);
        }
        event(new GoingToVerifyPayment($this));
        /** @var Order $order */
        /** @warning this @method withdrawWallet() is danger */
        $walletTransaction = $this->withdrawWallet();
        $gatewayVerifyResponse = ($payDriver ?? app(PayDriver::class))->setPayment($this)->verify();

        event(new PaymentVerified($this, $gatewayVerifyResponse));
        if ($gatewayVerifyResponse->success) {
            $this->invoice->status = Invoice::STATUS_SUCCESS;
            $this->invoice->save();
            return $this->invoice->payable->onSuccessPayment($this->invoice);
        } else {
            if ($walletTransaction) {
                $this->depositWallet($walletTransaction);
            }
            $this->invoice->status = Invoice::STATUS_FAILED;
            $this->invoice->save();
            return $this->invoice->payable->onFailedPayment($this->invoice);
        }
    }

    /**
     * @throws \Throwable
     */
    public function depositWallet($transaction1)
    {
        /**
         * @var Customer $customer
         */
        $customer = $this->invoice->payable->customer;

        $transaction2 = $customer->deposit(abs($transaction1->amount));

        $transaction1->delete();
        $transaction2->delete();

        return true;
    }

    public function goSuccess()
    {
        if (!in_array($this->invoice->status, [Invoice::STATUS_PENDING])) {
            throw new \LogicException(__('Only pending invoices can go success'));
        }
        $this->goForceSuccess();
    }

    public function goFailed()
    {
        if (!in_array($this->invoice->status, [Invoice::STATUS_PENDING])) {
            throw new \LogicException(__('Only pending invoices can go failed'));
        }
        $this->goForceFailed();
    }

    public function goForceSuccess()
    {
        $this->status = static::STATUS_SUCCESS;
        $this->save();
    }

    public function goForceFailed()
    {
        $this->status = static::STATUS_FAILED;
        $this->save();
    }

    public static function make(Invoice $invoice, $transactionId, $gateway)
    {
        $payment = new static([
            'transaction_id' => $transactionId,
            'gateway' => $gateway
        ]);
        $payment->invoice()->associate($invoice);
        $payment->save();

        return $payment;
    }

    public function getGatewayLabelAttribute()
    {
        $drivers = config('invoice.drivers');
        if ($this->gateway && isset($drivers[$this->gateway]) && isset($drivers[$this->gateway]['label'])) {
            return $drivers[$this->gateway]['label'];
        }
        return null;
    }

}
