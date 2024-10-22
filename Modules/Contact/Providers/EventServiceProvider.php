<?php

namespace Modules\Contact\Providers;

use Shetabit\Shopit\Modules\Contact\Providers\EventServiceProvider as BaseEventServiceProvider;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Contact\Events\ContactResponded;
use Modules\Contact\Listeners\SendEmailToStarter;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ContactResponded::class => [
            SendEmailToStarter::class
        ]
    ];
}
