<?php

namespace Modules\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderChangeStatusJobManager;
use Modules\Order\Services\Statuses\ChangeStatus;
use Throwable;
use Hekmatinasser\Verta\Verta;
use Shetabit\Shopit\Modules\Sms\Sms;
class ChangeOrderStatusInNewProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $all_order_ids = null;
    private $thisJob = null;

    private function add_to_order_ids_done(array $newOrderIds)
    {
        if (!$this->thisJob->order_ids_done) {
            // so this is first order_ids that are in progress
            $this->thisJob->order_ids_done = $newOrderIds;
            $this->thisJob->save();
        } else {
            $oldOrderIds = $this->thisJob->order_ids_done;
            $this->thisJob->order_ids_done = array_merge($oldOrderIds, $newOrderIds);
            $this->thisJob->save();
        }
    }

    private function get_all_order_ids($is_master)
    {
        if ($is_master) {
            return Order::query()
                ->with('childs')
                ->where('status',Order::STATUS_NEW)
                ->whereDate('created_at', date("Y-m-d", strtotime( '-1 days' ) ))
                ->whereNull('parent_id')
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->toArray();
        } else {
            $master = OrderChangeStatusJobManager::query()
                ->whereDate('run_time', '=', now()->format('Y-m-d'))
                ->where('is_master', '=', true)
                ->first();

            $masterAllOrderIds = json_decode($master->all_order_ids);
            $all_order_ids = $masterAllOrderIds;
            if ($master->order_ids_done) {
                $all_order_ids = array_diff($masterAllOrderIds, json_decode($master->order_ids_done));
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
            return array_values($all_order_ids);
        }
    }


    public function __construct()
    {
        //
    }


    public function handle()
    {
        $start_time = now();

        $cron_job_uuid = mt_rand(500000, 1999999);
        while (
            DB::table('order_change_status_job_managers')
                ->where('cron_job_uuid', $cron_job_uuid)->count() != 0
        ) {
            $cron_job_uuid = mt_rand(500000, 1999999);
        }

        Log::channel('ChangeStatus')->info("ChangeOrderStatusInNewProcess cronjob with uuid=$cron_job_uuid started!");
        usleep($cron_job_uuid);

        $todayJobs = OrderChangeStatusJobManager::query()
            ->whereDate('run_time', '=', now()->format('Y-m-d'))
            ->orderBy('run_time', 'desc')
            ->get();

        if ($todayJobs->count() == 0) {
            // this is new job for today. so we create a master CronJob
            $all_order_ids = $this->get_all_order_ids(true);
            $this->all_order_ids = $all_order_ids;

            $thisJob = OrderChangeStatusJobManager::create([
                'cron_job_uuid' => $cron_job_uuid,
                'run_time' => now(),
                'status' => 'started',
                'is_master' => true,
                'all_order_ids' => json_encode($all_order_ids),
            ]);
            $this->thisJob = $thisJob;
            // continue job
        } else {
            $masterJob = $todayJobs->where('is_master', '=', true)->first();
            $lastJob = $todayJobs->where('is_master', '=', false)
                ->first();

            if (in_array($masterJob->status, OrderChangeStatusJobManager::STATUSES_DENY_NEW_CRONJOB)) {
                Log::channel('ChangeStatus')->info("OrderChangeStatusJobManager: master cron job with uuid=$masterJob->cron_job_uuid & status=$masterJob->status is running so we kill this new cron job");
                return; /* kill job */
            } elseif ($lastJob && in_array($lastJob->status, OrderChangeStatusJobManager::STATUSES_DENY_NEW_CRONJOB)) {
                Log::channel('ChangeStatus')->info("OrderChangeStatusJobManager: a last cron job with uuid=$lastJob->cron_job_uuid & status=$lastJob->status is running so we kill this new cron job");
                return; /* kill job */
            }
            // so there is not cron job that be running. so we create a new
            $all_order_ids = $this->get_all_order_ids(false);
            // here some of order_ids might be done. so we don't get all of them. we just get those orders that hasn't changed
            $this->all_order_ids = $all_order_ids;

            $thisJob = OrderChangeStatusJobManager::create([
                'cron_job_uuid' => $cron_job_uuid,
                'run_time' => now(),
                'status' => 'started',
                'is_master' => false,
                'all_order_ids' => json_encode($all_order_ids),
            ]);
            $this->thisJob = $thisJob;
            $this->all_order_ids = $this->get_all_order_ids(false);
        }

// =====================================================================================================================
        $all_order_ids_for_chunk = $this->all_order_ids;
        Order::query()
            ->with(['childs', 'customer'])
//            ->where('status',Order::STATUS_NEW)
//            ->whereNull('parent_id')
            ->whereIn('id', $all_order_ids_for_chunk)
            ->orderBy('id', 'asc')
            ->chunk(50, function ($orders) {
                $orderIds = $orders->pluck('id')->toArray();
                $request = new Request();

                $request->merge([
                    'ids' => $orderIds,
                    'status' => Order::STATUS_IN_PROGRESS,
                ]);

//                $messages_list = [];
//                $notifications_list = [];
                foreach ($orders as $order) {
                    if ($order->status != Order::STATUS_NEW) { continue; }
                    \Illuminate\Support\Facades\DB::beginTransaction();
                    (new ChangeStatus($order, $request))->checkStatus(true);
                    if ($order->status != Order::STATUS_IN_PROGRESS) {
                        Log::channel('ChangeStatus')->warning("ChangeOrderStatusInNewProcess cronjob uuid=" . $this->thisJob->cron_job_uuid . " : order change status failed. order_id: " . $order->id . ' and DB::rollBack() called');
                        \Illuminate\Support\Facades\DB::rollBack();
                        continue;
                    }
                    $this->add_to_order_ids_done([$order->id]);
                    DB::table('pending_messages')->insert([
                        'template' => 'shopit-inprogress',
                        'token' => $order->id,
                        'mobile' => $order->customer->mobile,
                        'hold_to' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    DB::table('pending_notifications')->insert([
                        'notification_title' => 'ChangeStatusNotificationJob',
                        'model_id' => $order->id,
                        'hold_to' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    \Illuminate\Support\Facades\DB::commit();

//                    ChangeStatusNotificationJob::dispatch($order);
//                    $notifications_list[] = [
//                        'notification_title' => 'ChangeStatusNotificationJob',
//                        'model_id' => $order->id,
//                        'hold_to' => now(),
//                        'created_at' => now(),
//                        'updated_at' => now()
//                    ];
//
//                    $messages_list[] = [ /* we collect all shopit-inprogress messages in an array to send in ChangeOrderStatusMessageSender CronJob */
//                        'template' => 'shopit-inprogress',
//                        'token' => $order->id,
//                        'mobile' => $order->customer->mobile,
//                        'hold_to' => now(),
//                        'created_at' => now(),
//                        'updated_at' => now()
//                    ];
                }
                /* we send sms and notifications in ChangeOrderStatusMessageSender CronJob */
//                DB::table('pending_messages')->insert($messages_list);
//                DB::table('pending_notifications')->insert($notifications_list);
//                $this->add_to_order_ids_done($orders->pluck('id')->toArray());
                Log::channel('ChangeStatus')->info("ChangeOrderStatusInNewProcess cronjob uuid=" . $this->thisJob->cron_job_uuid . " this chunk has finished!");
            });

        $this->thisJob->status = OrderChangeStatusJobManager::STATUS_DONE;
        $this->thisJob->save();
        $different_time = now()->diffInMilliseconds($start_time);
        Log::channel('ChangeStatus')->info("ChangeOrderStatusInNewProcess cronjob uuid=" . $this->thisJob->cron_job_uuid . " finished successfully in $different_time milliseconds!. ChangeOrderStatusReviewer Job called to run");
    }
}
