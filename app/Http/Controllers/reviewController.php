<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Order\Entities\OrderChangeStatusJobManager;
use Modules\Order\Jobs\ChangeOrderStatusInNewProcess;
use Modules\Order\Jobs\ChangeOrderStatusReviewer;
use Modules\Order\Jobs\PendingMessagesSenderJob;
use Modules\Order\Jobs\PendingNotificationSenderJob;

class reviewController extends Controller
{
    public function reviewer()
    {
        $reviewer_uuid = Str::random(10);
        Log::info("ChangeOrderStatusReviewer CronJob started with uuid=$reviewer_uuid");
        $todayJobs = OrderChangeStatusJobManager::query()
            ->whereDate('run_time', '=', now()->format('Y-m-d'))
            ->orderBy('run_time', 'desc')
            ->get();

        $masterJob = $todayJobs->where('is_master', '=', true)->first();
        if ($todayJobs->count() == 0 || !$masterJob) {
            Log::info("ChangeOrderStatusReviewer CronJob uuid=$reviewer_uuid: no job runs today. so we call ChangeOrderStatusInNewProcess job to run. and also we call Reviewer job too after 7 minutes");
            // call main job
            ChangeOrderStatusInNewProcess::dispatch();
            // call reviewer job
            ChangeOrderStatusReviewer::dispatch()->delay(now()->addMinutes(7));
            return; // kill this job
        }

        // check last job
        $denyJobs =
            $todayJobs
                ->whereIn('status', OrderChangeStatusJobManager::STATUSES_DENY_NEW_CRONJOB)
                ->sortBy('run_time');
        if ($denyJobs) {
            foreach ($denyJobs as $denyJob) {
                if (now()->diffInSeconds($denyJob->run_time) <= 300) { /* every CronJob have 5 minutes time to do its works */
                    // there is a CronJob that is working. we should continue the process and run this job 5 minutes later
                    ChangeOrderStatusReviewer::dispatch()->delay(now()->addMinutes(5));
                    Log::info("ChangeOrderStatusReviewer CronJob uuid=$reviewer_uuid: another ChangeOrderStatusInNewProcess job with uuid=$denyJob->cron_job_uuid is running. so we don't do anything. it's run_time is for less than 5 minutes ago. we call ChangeOrderStatusReviewer for 5 minutes later");
                    return; // we don't call main job. just kill this job
                } else {
                    $denyJob->status = OrderChangeStatusJobManager::STATUS_FAILED;
                    $denyJob->save();
                }
            }
        }
        // ============================================================================
        // if some fields has not finished we should call main job and continue process
        $masterAllOrderIds = json_decode($masterJob->all_order_ids);
        $all_order_ids = $masterAllOrderIds;
        if ($masterJob->order_ids_done) {
            $all_order_ids = array_diff($masterAllOrderIds, json_decode($masterJob->order_ids_done));
        }

        // those jobs that are not master and also are not this job
        $notMasters = OrderChangeStatusJobManager::query()
            ->whereDate('run_time', '=', now()->format('Y-m-d'))
            ->where('is_master', '=', false)
            ->get();

        if ($notMasters) {
            foreach ($notMasters as $job) {
                if ($job->order_ids_done) {
                    $all_order_ids = array_diff($all_order_ids, json_decode($job->order_ids_done));
                }
            }
        }

        $lastJob = $todayJobs->sortByDesc('run_time')->first();
        if (count(array_values($all_order_ids)) > 0) {
            // update the last job status = need_another
            $lastJob->status = OrderChangeStatusJobManager::STATUS_NEED_ANOTHER;
            $lastJob->save();
            // run main job and run this job
            ChangeOrderStatusInNewProcess::dispatch();
            ChangeOrderStatusReviewer::dispatch()->delay(now()->addMinutes(7));
            Log::info("ChangeOrderStatusReviewer CronJob uuid=$reviewer_uuid: reviewer founded some order_ids that are not done so we run ChangeOrderStatusInNewProcess CronJob again and also reviewer after 7 minutes");
            return; // kill this job
        }

        $lastJob->status = OrderChangeStatusJobManager::STATUS_COMPLETED;
        $lastJob->save();

        Log::info("ChangeOrderStatusReviewer CronJob uuid=$reviewer_uuid: FINISHED -- CronJobs runs successfully. we call PendingMessagesSenderJob and PendingNotificationSenderJob twice with 5 minutes delay");
        // call PendingMessagesSenderJob now
        PendingMessagesSenderJob::dispatch();
        PendingMessagesSenderJob::dispatch()->delay(now()->addMinutes(5));
        // call PendingNotificationSenderJob now
        PendingNotificationSenderJob::dispatch();
        PendingNotificationSenderJob::dispatch()->delay(now()->addMinutes(5));
    }
}
