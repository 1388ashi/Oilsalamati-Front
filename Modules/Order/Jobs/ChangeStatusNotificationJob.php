<?php

namespace Modules\Order\Jobs;

use Hekmatinasser\Verta\Verta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Order\Entities\Order;
use Modules\Setting\Entities\Setting;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Order\Jobs\ChangeStatusNotificationJob as BaseChangeStatusNotificationJob;
use Shetabit\Shopit\Modules\Sms\Sms;
use Illuminate\Support\Str;

class ChangeStatusNotificationJob implements ShouldQueue
{
    public function firebase($order, $tokens)
    {
        if (empty($tokens)) {
            return;
        }
        $orderId =  $this->order->reserved_id ?: $this->order->id;
        $message =  (new FirebaseMessage())
            ->withTitle('وضعیت سفارش تغییر کرد')
            ->withBody("مشتری عزیز وضعیت سفارش شما به {$this->newStatus} تغییر کرد")
            ->withClickAction('order/' . $orderId);
        $message->asNotification(array_values(array_unique($tokens)));
    }

    public function database($order, $customerId)
    {
        $orderId =  $this->order->reserved_id ?: $this->order->id;
        DatabaseNotification::query()->create([
            'id' => Str::uuid(),
            'type' => 'order',
            'notifiable_type' => 'Modules\Customer\Entities\Customer',
            'notifiable_id' =>  $customerId,
            'data' => [
                'order_id' => $orderId,
                'description' => self::getMessage($this->order->status,$this->order->id,$this->newStatus)
            ],
            'read_at' =>  null,
            'created_at' =>  now(),
            'updated_at' =>  now(),
        ]);
    }

    public static function getMessage($newStatus,$orderId,$newStatusPersian)
    {
        $message = "مشتری عزیز وضعیت سفارش شما به {$newStatusPersian} تغییر کرد";

        if ($newStatus == Order::STATUS_DELIVERED){
            $message = 'سلام دوست عزیز، سفارش '.$orderId.' ارسال شده.
                        شما میتونید کد رهگیری پستی خود را از از طریق این صفحه https://benedito.ir/post-tracking بگیرید و مرسوله خود را پیگیری کنید.
                        ممنون از همراهی شما با خانواده بندیتو';
        }

        if ($newStatus == Order::STATUS_IN_PROGRESS){
            $message = 'سلام، با تشکر از شما ، سفارش '.$orderId.' هم اکنون به واحد تولید و بسته‌بندی انتقال داده شد و حداکثر طی ۷ روز کاری آینده به اداره پست تحویل داده می‌شود.';
        }

        if ($newStatus == Order::STATUS_NEW){
            $message = 'سلام دوست عزیز سفارش '.$orderId.' باموفقیت دریافت شد. شما تا 12 ظهر روز بعد فرصت دارید تا به سفارشتون محصولی اضافه کنید یا محصولی حذف کنید. سفارش بعد از انتقال به واحد تولید دیگر قابل تغییر نیست.';
        }

        return $message;
    }


    public function handle()
    {
        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'Modules\Customer\Entities\Customer')
            ->whereNotNull('device_token')
            ->where('tokenable_id', $this->order->customer_id)
            ->get('device_token')->pluck('device_token')->toArray();

        $via = explode(',', Setting::getFromName($this->order->status.'_type_sms'));
        if (in_array('firebase' , $via)){
            $this->firebase($this->order, $tokens);
        }
        if (in_array('sms' , $via)){
            $this->sms($this->order, $this->order->customer);
        }

        if (in_array('database' , $via)){
            $this->database($this->order, $this->order->customer_id);
        }
    }


    public function sms($order, $customer)
    {
        $coreSettings = app(CoreSettings::class);
        $orderId =  $this->order->reserved_id ?: $this->order->id;
        $pattern = $coreSettings->get('sms.patterns.change_status');
        $address = json_decode($order->address);
        $full_name = $customer->full_name ?: $address->first_name.' '.$address->last_name;

        // ترتیب این کلید ها به هیچ وجه نباید عوض بشه
        $data = [
            'full_name' => $full_name,
            'order_id' => $orderId,
            'status' => __('core::statuses.'.$order->status),
        ];

        if ($this->order->status == Order::STATUS_IN_PROGRESS) {
            if ($this->order->shipping_id == 4) {
                $output = Sms::pattern('motorcycle-courier')->data([
                    'code' => $this->order->id,
                ])->to([$customer->mobile])->send();

            } elseif ($this->order->shipping_id == 7) {
                $output = Sms::pattern('delivery-in-shop')->data([
                    'code' => $this->order->id,
                ])->to([$customer->mobile])->send();

            } else {
                $output = Sms::pattern('shopit-inprogress')->data([
                    'code' => $this->order->id,
                    'token2' => Verta::now()->addDay(8)->format('Y/m/d'),
                    'token3' => Verta::now()->addDay(10)->format('Y/m/d')
                ])->to([$customer->mobile])->send();
            }
        }
        // اگر اس ام اس اختصاصی نفرستادی دیفالت و بفرست
        elseif (!$this->sendCustomSms($order, $customer, $data)
            && in_array($order->status, [Order::STATUS_CANCELED, Order::STATUS_DELIVERED])
        ){
            Sms::pattern($pattern)->data($data)->to([$customer->mobile])->send();
        }
    }









    // came from vendor ================================================================================================
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $newStatus;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $order)
    {
        $this->newStatus = __('core::statuses.'.$order->status);
    }

    public function sendCustomSms($order, $customer, $data) {
        // چک کنیم آیا اس ام اس اختصاصی برای این وضعیت وجود دارد
        $coreSettings = app(CoreSettings::class);
        $customPattern = $coreSettings->get('sms.custom_patterns.order_' . $order->status);

        if ($customPattern && isset($customPattern['name'])) {
            if (isset($customPattern['keys'])) {
                // فقط کلید هایی که مشخص شده رو میفرستیم
                $newData = Helpers::getArrayIndexes($data, $customPattern['keys']);
            } else {
                $newData = $data;
            }
            Sms::pattern($customPattern['name'])->data($newData)->to([$customer->mobile])->send();

            return true;
        }

        return false;
    }

}
