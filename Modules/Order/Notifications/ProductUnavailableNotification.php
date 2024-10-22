<?php

namespace Modules\Order\Notifications;

//use Shetabit\Shopit\Modules\Order\Notifications\ProductUnavailableNotification as BaseProductUnavailableNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Admin\Entities\Admin;
use Modules\Core\Channels\FirebaseChannel;
use Modules\Core\Entities\Permission;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Emails\ListenChargeMail;
use Modules\Product\Entities\Product;

class ProductUnavailableNotification extends Notification
{
    use Queueable;

    private array|\Illuminate\Database\Eloquent\Collection $admins;

    /**
     *زمانی که موجودی یک محصول به اتمام برسه
     * توی ماژول انبار وضعیتش به ناموجود تغییر میکنه
     */
    public function __construct(public Product $product)
    {
        $this->admins = Admin::query()
            ->select(['id','email'])
            ->with('roles')->get();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database', FirebaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {

        $admins = $this->admins->whereNotNull('email')->all();
        $emails = [];
        /** @var Admin $admin */
        foreach ($admins as $admin) {
            $permissions = optional($admin->roles->first())->hasAnyPermission(['write_product','write_store']);
            if($permissions || $admin->hasRole('super_admin')){
                $emails[] = $admin->email;
            };
        }
        if (empty($emails)){
            return;
        }
        Mail::to($emails)->send(new ListenChargeMail($this->product));
//        ->line('سلام ادمین عزیز')
//        ->line($this->product->id.'با شناسه '.$this->product->title ."محصول ")
//        ->line('ناموجود شد');
    }

    public function toDatabase()
    {
        foreach ($this->admins as $admin) {
            DatabaseNotification::query()->create([
                'id' => \Str::uuid(),
                'type' => 'ProductUnavailableNotification',
                'notifiable_type' => 'Modules\Admin\Entities\Admin',
                'notifiable_id' =>  $admin->id,
                'data' =>  json_encode([
                    'product_id' => $this->product->id,
                    'description' => "مشتری عزیز {$this->product->title} شما موجود شد."
                ]),
                'read_at' =>  null,
                'created_at' =>  now(),
                'updated_at' =>  now(),
            ]);
        }
    }

    public function toFirebase($notifiable)
    {
        $adminIds = $this->pluck('id')->toArray();
        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'Modules\Admin\Entities\Admin')
            ->whereNotNull('device_token')
            ->whereIn('id', $adminIds)
            ->get('device_token')->pluck('device_token')->toArray();
        if (empty($tokens)) {
            return;
        }
        $mainImage = $this->product->main_image;
        $message =  (new FirebaseMessage())
            ->withTitle('موجودی ناموجود شد')
            ->withBody($this->product->id.'با شناسه '.$this->product->title ."محصول ")
            ->withClickAction('product/' . $this->product->id);
        if ($mainImage) {
            $message->withImage($mainImage->getFullUrl());
        }
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
