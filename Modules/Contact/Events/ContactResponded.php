<?php

namespace Modules\Contact\Events;

//use Shetabit\Shopit\Modules\Contact\Events\ContactResponded as BaseContactResponded;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Modules\Contact\Entities\Contact;
use Modules\Contact\Entities\Response;

class ContactResponded
{
    use Dispatchable, SerializesModels;
    /**
     * @var Contact
     */
    public $contact;
    /**
     * @var Response
     */
    public $response;

    /**
     * Create a new event instance.
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
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
