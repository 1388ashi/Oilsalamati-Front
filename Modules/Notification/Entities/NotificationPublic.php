<?php

namespace Modules\Notification\Entities;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class NotificationPublic extends Model
{
    protected $table = 'notifications_public';
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
