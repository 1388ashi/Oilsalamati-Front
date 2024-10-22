<?php

namespace Modules\Core\Emails;

//use Shetabit\Shopit\Modules\Core\Emails\NewOrderMail as BaseNewOrderMail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public $order)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('core::email.new_order')
            ->subject('سفارش شما ثبت شد')
            ->with(['order' => $this->order]);;
    }
}
