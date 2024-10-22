<?php

namespace Modules\Core\Services;

use Shetabit\Shopit\Modules\Core\Services\NotificationService as BaseNotificationService;




use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Core\Contracts\Notifiable;
use Modules\Customer\Entities\Customer;

class NotificationService
{
    protected $paginationNumber = 20;

    public function __construct(public Notifiable $notifiable) {}

    public function get(Carbon $lastCreatedAt = null): \Illuminate\Database\Eloquent\Collection
    {
        /** @var MorphMany $notificationBuilder */
        $notificationBuilder = $this->notifiable->notifications()->limit($this->paginationNumber);
        if ($lastCreatedAt !== null) {
            $notificationBuilder->where('created_at', '<', $lastCreatedAt);
        }

        return $notificationBuilder->get(Customer::NOTIFICATION_FIELDS);
    }

    public function read()
    {
        $this->notifiable->notifications()->whereNull('read_at')->update([
            'read_at' => now()
        ]);
    }

    public function getTotalUnread()
    {
        return $this->notifiable->notifications()->whereNull('read_at')->count();
    }
}
