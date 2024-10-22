<?php

namespace Modules\CustomersClub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBirthDateSms implements ShouldQueue
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
    public function handle()
    {
        Log::info('Send Birthdate SMS CronJob Started');
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            (new \Modules\CustomersClub\Helpers\Helpers)->generateDiscountCodeForBirthDate();
            \Illuminate\Support\Facades\DB::commit();
            Log::info('ChangeOrderStatusInNewProcess cronjob ran!');
        } catch (Throwable $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('catch Send Birthdate SMS:'.$exception->getMessage() . $exception->getTraceAsString());
            return response()->error($exception->getMessage());
        }
        return response()->success('تخفیف تولد مشتریان تعیین شد و پیامک های آن ارسال شد.', null);
    }
}
