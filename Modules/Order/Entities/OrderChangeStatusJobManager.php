<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderChangeStatusJobManager extends Model
{
    protected $fillable = ['cron_job_uuid','run_time','status','is_master','all_order_ids','order_ids_done'];

    const STATUS_STARTED = 'started';
    const STATUS_DONE = 'done';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NEED_ANOTHER = 'need_another';


    const STATUSES_ALLOW_NEW_CRONJOB = [
        OrderChangeStatusJobManager::STATUS_FAILED,
        OrderChangeStatusJobManager::STATUS_NEED_ANOTHER,
    ];
    const STATUSES_DENY_NEW_CRONJOB = [
        OrderChangeStatusJobManager::STATUS_STARTED,
        OrderChangeStatusJobManager::STATUS_COMPLETED,
        OrderChangeStatusJobManager::STATUS_DONE,
    ];



    public static function getAllStatuses() {
        return [
            static::STATUS_STARTED,
            static::STATUS_DONE,
            static::STATUS_FAILED,
            static::STATUS_COMPLETED,
            static::STATUS_NEED_ANOTHER
        ];
    }
}
