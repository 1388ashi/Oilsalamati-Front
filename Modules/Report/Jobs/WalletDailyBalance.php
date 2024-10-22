<?php

namespace Modules\Report\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Modules\Report\Http\Controllers\Admin\ReportController;

class WalletDailyBalance implements ShouldQueue
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
        $date = date('Y-m-d');
        $previousDate = date('Y-m-d', strtotime($date . ' -1 day')); // Subtract 1 day from the current date

        $dateTime = date('Y-m-d H:i:s');
        $balance = (new ReportController)->getTotalWallet();
        $wdb = new \Modules\Report\Entities\WalletDailyBalance();
        $wdb->date = $previousDate;
        $wdb->balance = $balance;
        $wdb->save();

        $balance = number_format($balance, 0 , '.' , ',' );

        Log::info('موجودی کیف پول برای تاریخ ' . $previousDate . ' با مبلغ ' . $balance . ' با موفقیت ثبت شد');
    }
}
