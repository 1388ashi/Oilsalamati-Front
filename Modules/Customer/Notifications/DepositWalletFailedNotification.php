<?php

namespace Modules\Customer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kutia\Larafirebase\Channels\FirebaseChannel;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Core\Channels\SmsChannel;
use Modules\Core\Helpers\Helpers;
use Modules\Order\Entities\Order;
use Modules\Setting\Entities\Setting;
use Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;

class DepositWalletFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public $customer , public $amount)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', FirebaseChannel::class];
    }

    public function toDatabase()
    {
        return [
            'amount' => $this->amount,
            'invoice_action' => 'unpaid',
            'description' => "شارژ کیف پول شما ناموفق بود",
            'tracking_code' =>  null
        ];
    }

    public function toFirebase()
    {
       $tokens =  DB::table('personal_access_tokens')
            ->where('tokenable_type', "Modules\Customer\Entities\Customer")
            ->whereNotNull('device_token')
            ->where('tokenable_id', $this->customer->id)
            ->get('device_token')
            ->pluck('device_token')->toArray();

       $amount = number_format($this->amount);
        $message =  (new FirebaseMessage())
            ->withTitle('شارژ ناموفق')
            ->withBody("عملیات شارژ کیف پول شمابه مبلغ {$amount} ناموفق بود")
            ->withImage(url('assets/images/wallet.jpg'));
        $message->asNotification(array_values(array_unique($tokens)));
    }


    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
