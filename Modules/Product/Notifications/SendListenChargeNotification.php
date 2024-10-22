<?php

namespace Modules\Product\Notifications;

//use Shetabit\Shopit\Modules\Product\Notifications\SendListenChargeNotification as BaseSendListenChargeNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Core\Channels\FirebaseChannel;
use Modules\Core\Channels\SmsChannel;
use Modules\Product\Entities\ListenCharge as ListenChargeList;
use Modules\Product\Entities\Product;
use Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;

// UNUSED
class SendListenChargeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Product $product;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct( Product $product)
    {
        $productId = $product->id;
        $this->product = $product->replicate();
        $this->product->id = $productId;
        $this->product->unsetRelation('tags');
        $this->product->unsetRelation('unit');
        $this->product->unsetRelation('sizeCharts');
        $this->product->unsetRelation('brand');
        $this->product->unsetRelation('activeFlash');
        $this->product->unsetRelation('specifications');
        $this->product->unsetRelation('categories');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', FirebaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('سلام مشتری عزیز')
            ->line($this->product->title ."محصول ")
            ->line('موجود شد');
    }

    public function toDatabase()
    {
        return [
            'product_id' => $this->product->id,
            'description' => "مشتری عزیز {$this->product->title} شما موجود شد."
        ];
    }

    public function toFirebase($notifiable)
    {
        $mainImage = $this->product->main_image;
        $tokens = $notifiable->getPushTokens();
        if (empty($tokens)) {
            return;
        }
        $message =  (new FirebaseMessage())
            ->withTitle('موجودی جدید')
            ->withBody("محصول {$this->product->title} موجود شده است.")
            ->withClickAction('product/' . $this->product->id);
        if ($mainImage) {
            $message->withImage($mainImage->getFullUrl());
        }

        return $message->asNotification($tokens);
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
