<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Archiver\Jobs\ArchiveBigTablesJob;
use Modules\Archiver\Jobs\ArchiveNotificationTableJob;
use Modules\Archiver\Jobs\DeleteUnusablePersonalAccessTokensJob;
use Modules\CustomersClub\Jobs\RemoveDuplicateScores;
use Modules\CustomersClub\Jobs\SendBirthDateSms;
use Modules\Home\Jobs\BeniboxEmptySmsJob;
use Modules\Order\Jobs\ChangeOrderStatusInNewProcess;
use Modules\Order\Jobs\ChangeOrderStatusReviewer;
use Modules\Order\Jobs\ChangeStatusToFailedJob;
use Modules\Order\Jobs\UpdateChargeTypeOfTransactionsJob;
use Modules\Product\Jobs\CheckDiscountUntilJob;
use Modules\Report\Jobs\WalletDailyBalance;
use Shetabit\Shopit\Modules\Core\Console\Kernel as BaseKernel;
use Shetabit\Shopit\Modules\Order\Jobs\RemoveFailedOrdersJob;
use Shetabit\Shopit\Modules\Order\Jobs\ReservationTimeOutNotificationJob;
//use Shetabit\Shopit\Modules\Product\Jobs\CheckDiscountUntilJob;

class Kernel extends BaseKernel {

    protected function schedule(Schedule $schedule)
    {
        /*
         * very important attention: ChangeOrderStatusReviewer should run 7 minutes after ChangeOrderStatusInNewProcess
         * */
        $schedule->job(ChangeOrderStatusInNewProcess::class)->dailyAt('12:00');
        $schedule->job(ChangeOrderStatusReviewer::class)->dailyAt('12:07');
        $schedule->job(UpdateChargeTypeOfTransactionsJob::class)->hourlyAt('20');
        $schedule->command('shopit:sitemap')->weeklyOn(5, '04:00');
        $schedule->job(ChangeStatusToFailedJob::class)->at('05:00');
        //$schedule->job(ReservationTimeOutNotificationJob::class)->everyMinute(); Benedito reserve nadare
        $schedule->job(CheckDiscountUntilJob::class)->everyFiveMinutes();
        $schedule->job(RemoveFailedOrdersJob::class)->dailyAt('02:00');
//        $schedule->job(ArchiveBigTablesJob::class)->weekly();
//        $schedule->job(ArchiveNotificationTableJob::class)->monthly();
//        $schedule->job(DeleteUnusablePersonalAccessTokensJob::class)->weekly();
        $schedule->job(WalletDailyBalance::class)->timezone('Asia/Tehran')->dailyAt('00:00');
        $schedule->job(SendBirthDateSms::class)->timezone('Asia/Tehran')->dailyAt('20:00'); // تولید کد تخفیف تولد و ارسال پیامک
        $schedule->job(RemoveDuplicateScores::class)->everyThirtyMinutes(); // حذف امتیازات تکراری باشگاه مشتریان

        $schedule->job(BeniboxEmptySmsJob::class)->everyMinute();
    }

}
