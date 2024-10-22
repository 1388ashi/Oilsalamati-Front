<?php

namespace Modules\Contact\Listeners;

//use Shetabit\Shopit\Modules\Contact\Listeners\SendEmailToStarter as BaseSendEmailToStarter;

use Illuminate\Support\Facades\Mail;
use Modules\Contact\Events\ContactResponded;
use Modules\Contact\Mail\SendContactResponse;

class SendEmailToStarter
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ContactResponded $event
     * @return void
     */
    public function handle(ContactResponded $event)
    {
        $contact = $event->contact;
        $response = $event->response;

        Mail::to($contact->email)->send(new SendContactResponse($contact, $response));
    }
}
