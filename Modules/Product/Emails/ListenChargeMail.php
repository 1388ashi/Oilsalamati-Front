<?php

namespace Modules\Product\Emails;

//use Shetabit\Shopit\Modules\Product\Emails\ListenChargeMail as BaseListenChargeMail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Product\Entities\Product;

class ListenChargeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Product $product)
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
        return $this->view('core::product.listenCharge')
            ->subject('محصول مورد نظر شما موجود شد')
            ->with(['product' => $this->product]);
    }
}
