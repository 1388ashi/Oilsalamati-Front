<?php

namespace Modules\Notification\Entities;

//use Shetabit\Shopit\Modules\Notification\Entities\Notification as BaseNotification;

use DateTimeInterface;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
