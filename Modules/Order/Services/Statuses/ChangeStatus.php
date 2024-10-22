<?php

namespace Modules\Order\Services\Statuses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\ChargeType;
use Modules\Customer\Entities\Customer;
use Modules\CustomersClub\Entities\CustomersClubLevel;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\Invoice\Entities\Invoice;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderStatusLog;
use Modules\Setting\Entities\Setting;
use Modules\Store\Entities\Store;
use Shetabit\Shopit\Modules\Sms\Sms;
//use Shetabit\Shopit\Modules\Order\Services\Statuses\ChangeStatus as BaseChangeStatus;

class ChangeStatus
{
    private bool $is_pending_messages;
    const SUCCESS_STATUS = [Order::STATUS_NEW, Order::STATUS_DELIVERED, Order::STATUS_IN_PROGRESS, Order::STATUS_RESERVED];
    const FAILED_STATUS = [Order::STATUS_CANCELED, Order::STATUS_FAILED, Order::STATUS_WAIT_FOR_PAYMENT, Order::STATUS_CANCELED_BY_USER];

    public function __construct(
        public Order $order,
        /* todo: we should delete this $request and use of before after status instead */
        public Request $request
    ) {
        $this->customer = $this->order->customer;
    }

    public function checkStatus($is_pending_messages = false)
    {
        $this->is_pending_messages = $is_pending_messages;
        $beforeStatus = $this->order->status;
        $newStatus = $this->request->status;


        if ($this->request->status == $this->order->status)
            return $this->order;

        // from failed =========> to success not new
        if (in_array($beforeStatus, static::FAILED_STATUS) && (in_array($newStatus, self::SUCCESS_STATUS) && $newStatus != Order::STATUS_NEW)) {
            if (request()->header('Accept') == 'application/json') {
                return redirect()->back()->with('error', 'از وضعیت های لغو شده فقط می توانید به وضعیت در انتظار تکمیل تغییر وضعیت دهید.');
            }
            // throw Helpers::makeValidationException('از وضعیت های لغو شده فقط می توانید به وضعیت در انتظار تکمیل تغییر وضعیت دهید');
        }
        // from failed =========> to new
        if (in_array($beforeStatus, self::FAILED_STATUS) && $newStatus == Order::STATUS_NEW) {
            $this->checkStoreBalance();
            $this->payByWallet();
        }
        // from new ======> to failed
        elseif ($beforeStatus == Order::STATUS_NEW && in_array($newStatus, self::FAILED_STATUS)) {
            $this->depositOrderAmountToWallet($newStatus);
            $this->returnOrderItemsToStore();
        }
        // from new ==========> to success not new
        elseif ($beforeStatus == Order::STATUS_NEW && (in_array($newStatus, self::SUCCESS_STATUS) && $newStatus != Order::STATUS_NEW)) {
            $giftAmount = $this->depositGift();
            $this->insertCustomersClubScores();
            $this->sendGiftMessage($giftAmount);
        }
        // from success not new ======> to failed
        elseif ((in_array($beforeStatus, self::SUCCESS_STATUS) && $beforeStatus != Order::STATUS_NEW) && in_array($newStatus, self::FAILED_STATUS)) {
            $this->takeBackGift();
            $this->takeBackCustomersClubScores();
            $this->depositOrderAmountToWallet($newStatus);
            $this->returnOrderItemsToStore();
        }
        // from success not new ======> to new
        elseif ((in_array($beforeStatus, self::SUCCESS_STATUS) && $beforeStatus != Order::STATUS_NEW) && $newStatus == Order::STATUS_NEW) {
            $this->takeBackGift();
            $this->takeBackCustomersClubScores();
        }

        // from success not new ======> to success not new it needs no work
        // from failed =========> to failed it needs no work

        // update delivered_at field
        if ($newStatus == Order::STATUS_DELIVERED)
            $this->order->update(['delivered_at' => now()]);

        return $this->setStatus($newStatus);
    }

    private function depositOrderAmountToWallet($newStatus): void
    {
        if (\request('no_charge'))
            return;

        $deposit = $this->customer->deposit($this->order->total_invoices_amount, [
            'causer_id' => $this->order->customer_id,
            'causer_mobile' => auth()->user()->mobile,
            'description' => "برگشت مبلغ سفارش در اثر تغییر وضعیت به {$newStatus}",
        ]);


        $this->order->invoices()->create([
            'amount' => $this->order->total_invoices_amount * -1,
            'wallet_amount' => ($this->order->total_invoices_amount) * -1,
            'gift_wallet_amount' => ($this->order->paid_by_wallet_gift_balance) * -1,
            'type' => Invoice::PAY_TYPE_WALLET,
            'transaction_id' => $deposit->id,
            'status' => Invoice::STATUS_SUCCESS
        ]);

        $user_wallet_gift_balance = DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $this->order->customer_id)
            ->latest('id')
            ->first()->gift_balance;

        DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $this->order->customer_id)
            ->update([
                'gift_balance' => $user_wallet_gift_balance + $this->order->paid_by_wallet_gift_balance,
            ]);
    }
    private function returnOrderItemsToStore(): void
    {
        $orderItems = $this->order->items()->where('status', 1)->get();
        foreach ($orderItems as $item) {
            Store::insertModel((object)
                [
                    'variety_id' => $item->variety_id,
                    'description' => "با تغییر وضغیت سفارش با شناسه {$item->order_id} به انبار اضافه شد",
                    'type' => Store::TYPE_INCREMENT,
                    'quantity' => $item->quantity
                ]);
        }
        foreach ($this->order->childs as $child) {
            $orderItems = $child->items()->where('status', 1)->get();
            foreach ($orderItems as $item) {
                Store::insertModel((object)
                    [
                        'variety_id' => $item->variety_id,
                        'description' => "با تغییر وضغیت سفارش با شناسه {$item->order_id} به انبار اضافه شد",
                        'type' => Store::TYPE_INCREMENT,
                        'quantity' => $item->quantity
                    ]);
            }
        }
    }
    private function takeBackGift(): void
    {
        $giftTransaction = DB::table('transactions')->find($this->order->gift_transaction_id);
        if (!$giftTransaction)
            return;
        $giftAmount = $giftTransaction->amount;

        $gift_text = "بازپس گیری هدیه سفارش " . $this->order->id;
        $metaData = [
            'causer_id' => $this->order->customer_id,
            'causer_mobile' => $this->order->customer->mobile,
            'description' => $gift_text,
            'order_id' => $this->order->id,
        ];

        $user_wallet_gift_balance = DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $this->order->customer_id)
            ->latest('id')
            ->first()->gift_balance;

        if ($user_wallet_gift_balance < $giftAmount)
            throw Helpers::makeValidationException('موجودی هدیه از هدیه ی این سفارش کمتر است.');

        $this->customer->withdraw($giftAmount, $metaData);
        // set orders.gift_transaction_id to the null.
        DB::table('orders')->where('id', $this->order->id)->update(['gift_transaction_id' => null]);
        // update gift wallet balance
        DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $this->order->customer_id)->update([
                    'gift_balance' => $user_wallet_gift_balance - $giftAmount,
                ]);
    }
    private function takeBackCustomersClubScores(): void
    {
        CustomersClubScore::takeBackOrderScores($this->order);
    }
    private function depositGift(): int
    {
        $totalOrderAmountWithoutShipping = $this->order->total_invoices_amount - $this->order->total_shipping_amount;
        $customerLevelDiscountPercentage = CustomersClubLevel::where('id', $this->order->customer->customers_club_level['id'])->value('permanent_purchase_discount');
        $giftAmount = $totalOrderAmountWithoutShipping / 100 * $customerLevelDiscountPercentage;


        $gift_text = "هدیه خرید سفارش " . $this->order->id;

        $metaData = [
            'causer_id' => $this->order->customer_id,
            'causer_mobile' => $this->order->customer->mobile,
            'description' => $gift_text,
            'order_id' => $this->order->id,
        ];


        // we check that this order's gift has deposited or not?
        if ($this->order->gift_transaction_id) {
            Log::warning("order gift has deposited once with transaction_id: " . $this->order->gift_transaction_id . " for order_id: " . $this->order->id . " so we don't deposit again");
            return 0;
        }


        $deposit = $this->customer->deposit($giftAmount, $metaData);
        DB::table('orders')->where('id', $this->order->id)->update(['gift_transaction_id' => $deposit->id]);

        $user_wallet_gift_balance = DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $this->order->customer_id)
            ->latest('id')
            ->first()->gift_balance;

        DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $this->order->customer_id)->update([
                    'gift_balance' => $user_wallet_gift_balance + $giftAmount,
                ]);

        return $giftAmount;
    }
    private function insertCustomersClubScores(): void
    {
        CustomersClubScore::insertOrderScores($this->order);
    }
    private function checkStoreBalance(): void
    {
        foreach ($this->order->items()->active()->get() as $item) {
            if ($item->variety->store->balance < $item->quantity)
                throw Helpers::makeValidationException("موجودی محصول {$item->variety->product->title} انبار کافی نیست");
        }
        foreach ($this->order->childs as $child) {
            foreach ($child->items()->active()->get() as $item) {
                if ($item->variety->store->balance < $item->quantity)
                    throw Helpers::makeValidationException("موجودی محصول {$item->variety->product->title} انبار کافی نیست");
            }
        }

    }
    private function payByWallet(): void
    {
        $this->order->payWithWallet($this->customer);

        // delete it. I think this is unUsable
//        Invoice::withoutEvents(function (){
//            $this->order->invoices()->latest('id')->first()->delete();
//        });
        $this->order->orderLogs()->create([
            'amount' => $this->order->total_invoices_amount,
            'status' => $this->request->status
        ]);

        //childs
        $childs = Order::query()
            ->where('parent_id', $this->order->id)
            ->where('status', $this->order->status)
            ->get();

        if ($childs) {
            foreach ($childs as $child) {
                $child->payWithWallet($this->customer);
                // delete it. I think this is unUsable
//                Invoice::withoutEvents(function () use($child){
//                    $child->invoices()->latest('id')->first()->delete();
//                });
                $child->orderLogs()->create([
                    'amount' => $child->getTotalAmount(),
                    'status' => $this->request->status
                ]);
            }
        }
    }

    private function sendGiftMessage($giftAmount): void
    {
        if (env('APP_ENV') != 'production')
            return;

        if (!\request()->has('dont_send_sms') || !\request()->dont_send_sms) {
            $customer = $this->order->customer;
            try {
                $pattern = app(CoreSettings::class)->get('sms.patterns.customer_success_payments_gift');
                $address = Address::find($this->order->address_id);
                $name = 'کاربر';
                if (isset($address->first_name)) {
                    $name = explode(' ', $address->first_name)[0];
                }
                if ($this->is_pending_messages) {
                    DB::table('pending_messages')->insert([
                        'template' => $pattern,
                        'mobile' => $customer->mobile,
                        'hold_to' => now(),
                        'token' => $name,
                        'token2' => number_format($giftAmount),
                        'token3' => number_format($customer->balance),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    Sms::pattern($pattern)->data([
                        'token' => $name,
                        'token2' => number_format($giftAmount),
                        'token3' => number_format($customer->balance)
                    ])->to([$customer->mobile])->send();
                }
            } catch (\Exception $exception) {
                Log::debug('Exception:' . $exception->getMessage());
            }
        }
    }




    private function setStatus($status): Order
    {
        $childs = Order::query()
            ->where('parent_id', $this->order->id)
            ->where('status', $this->order->status)
            ->get();

        foreach ($childs as $child) {
            OrderStatusLog::store($child, $child->status);
            $child->status = $status;
            $child->save();
        }

        OrderStatusLog::store($this->order, $this->order->status);
        $this->order->status = $status;
        $this->order->save();

        return $this->order;
    }

}
