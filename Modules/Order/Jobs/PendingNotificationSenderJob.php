<?php

namespace Modules\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\PendingNotification;

class PendingNotificationSenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uuid = Str::random(10);
        Log::info("PendingNotificationSenderJob uuid=$uuid started.");

        $notifications_list = PendingNotification::query()->where('hold_to', '<', now())->get();
        if ($notifications_list->count() == 0) {
            Log::info("PendingNotificationSenderJob uuid=$uuid: there was no pending_notification");
            return;
        }

        foreach ($notifications_list as $notification) {
            switch ($notification->notification_title) {
                case 'ChangeStatusNotificationJob':
                    $this->ChangeStatusNotificationJob($notification);
                    break;
            }
            $notification->delete();
        }
        Log::info("PendingNotificationSenderJob uuid=$uuid FINISHED.");
    }

    private function ChangeStatusNotificationJob($notification)
    {
        $order = Order::find($notification->model_id);
        if ($order) {
            ChangeStatusNotificationJob::dispatch($order);
        }

    }
}
