<?php

namespace Modules\Newsletters\Emails;

//use Shetabit\Shopit\Modules\Newsletters\Emails\NewslettersEmail as BaseNewslettersEmail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Newsletters\Entities\Newsletters;

class NewslettersEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Newsletters $newsletters
    ){}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $site = config('app.name');

        return $this
            ->from("{$site}@gmail.com" , $site)
            ->subject($this->newsletters->title)
            ->view('core::newsletter.email')
            ->with(['newsletters' => $this->newsletters]);
    }
}
