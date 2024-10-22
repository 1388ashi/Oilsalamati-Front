<?php

namespace Modules\Order\Jobs;

use Hekmatinasser\Verta\Verta;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Order\Entities\PendingMessage;
use Shetabit\Shopit\Modules\Sms\Sms;

class PendingMessagesSenderJob implements ShouldQueue
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


    private $failed_messages_list = [];



    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uuid = Str::random(10);
        Log::info("PendingMessagesSenderJob uuid=$uuid started.");
        $messages_list = PendingMessage::query()->where('hold_to', '<', now())->get();
        if ($messages_list->count() == 0) {
            Log::info("PendingMessagesSenderJob uuid=$uuid: there was no pending_message");
            // Log
            return;
        }

        foreach ($messages_list as $message) {
            switch ($message->template) {
                case 'shopit-inprogress':
                    $this->shopit_inprogress($message);
                    break;

                case 'customer-success-payments-gift':
                    $this->customer_success_payments_gift($message);
                    break;
            }
            $message->delete();
        }
        if (count($this->failed_messages_list) != 0) {
            // Log
            Log::info("PendingMessagesSenderJob uuid=$uuid : failed_messages is: " . serialize($this->failed_messages_list));
        }
        Log::info("PendingMessagesSenderJob uuid=$uuid FINISHED.");
    }

    private function shopit_inprogress($message)
    {
        if (env('APP_ENV') != 'local') {
            $output = Sms::pattern($message->template)->data([
                'code' => $message->token,
                'token2' => $message->token2,
                'token3' => $message->token3
            ])->to([$message->mobile])->send();
        } else {
            $output['status'] = 200;
        }


        if ($output['status'] != 200) {
            $this->failed_messages_list[] = $message->toArray();
        }
    }

    private function customer_success_payments_gift($message)
    {
        if (env('APP_ENV') != 'local') {
            $output = Sms::pattern($message->template)
                ->data([
                    'token' => $message->token,
                    'token2' => $message->token2,
                    'token3' => $message->token3
                ])->to([$message->mobile])->send();
        } else {
            $output['status'] = 200;
        }

        if ($output['status'] != 200) {
            $this->failed_messages_list[] = $message->toArray();
        }
    }
}
