<?php

namespace Modules\Contact\Mail;

use Shetabit\Shopit\Modules\Contact\Mail\SendContactResponse as BaseSendContactResponse;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Contact\Entities\Contact;
use Modules\Contact\Entities\Response;

class SendContactResponse extends Mailable
{
    use Queueable, SerializesModels;

    protected $contact;
    protected $response;

    /**
     * Create a new message instance.
     *
     * @param Contact $contact
     * @param Response $response
     */
    public function __construct(Contact $contact, Response $response)
    {
        $this->contact = $contact;
        $this->response = $response;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('contact.email_contact'), config('app.name'))
            ->subject('به تماس شما پاسخ داده شد!')
            ->with([
                'subject' => $this->contact->subject,
                'body' => $this->contact->body,
                'answer' => $this->response->text
            ])
            ->markdown('contact::emails.response');
    }
}
