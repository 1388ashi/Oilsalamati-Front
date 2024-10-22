<?php

namespace Modules\Customer\Events;

//use Shetabit\Shopit\Modules\Customer\Events\SmsVerify as BaseSmsVerify;

use Illuminate\Queue\SerializesModels;
use Modules\Customer\Entities\Customer;

class SmsVerify
{
    use SerializesModels;

    /**
     * @var string
     */
    public string $mobile;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $mobile)
    {
        $this->mobile = $mobile;
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
