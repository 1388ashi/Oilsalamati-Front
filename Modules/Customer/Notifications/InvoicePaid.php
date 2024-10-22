<?php

namespace Modules\Customer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Core\Channels\SmsChannel;
use Modules\Order\Entities\Order;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Customer\Notifications\InvoicePaid as BaseInvoicePaid;
use Shetabit\Shopit\Modules\Sms\Sms;

class InvoicePaid extends Notification implements ShouldQueue
{
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    // came from vendor ================================================================================================
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $customer = $order->customer;
        $activeItems = $order->activeItems;
        $this->order = $order->withoutRelations();
        $this->order->setRelation('customer', $customer);
        $this->order->setRelation('activeItems', $activeItems);
    }


    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $siteName = config('app.name');
        $items = $this->order->items()->with(['variety.product', 'variety.attributes'])->get();
        return (new MailMessage)
            ->from("{$siteName}@gmail.com", $siteName)
            ->view('core::email.new_order', ['order' => $this->order, 'items' => $items]);
    }

    public function toDatabase()
    {
        return [
            'amount' => $this->order->getTotalAmount(),
            'invoice_action' => 'paid',
            'description' => 'خرید شما با موفقیت انجام شد.',
            'tracking_code' =>  $this->order->reserved_id ?: $this->order->id
        ];
    }

    public function toSms($notifiable)
    {
        $customer = $this->order->customer;
        $address = json_decode($this->order->address);
        if (empty($this->order->customer->first_name)){
            $full_name = $address->first_name.' '.$address->last_name;
        }else{
            $full_name = $customer->first_name.' '. $customer->last_name;
        }

        $coreSettings = app(CoreSettings::class);
        $pattern = $coreSettings->get('sms.patterns.new_order');

        // ترتیبش به هیچ وجه نباید دست بخوره
        $data = [
            'full_name' => $full_name,
            'order_id' => $this->order->id,
            'status' => __('core::statuses.'.$this->order->status),
            'transaction_id' => $this->order->id,
        ];

        $customKeys = $coreSettings->get('sms.new_order', []);
        if (isset($customKeys['keys'])) {
            $data = Helpers::getArrayIndexes($data, $customKeys['keys']);
        }

        Sms::pattern($pattern)->data($data)->to([$customer->mobile])->send();
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
