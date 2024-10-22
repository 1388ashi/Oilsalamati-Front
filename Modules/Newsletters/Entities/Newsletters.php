<?php

namespace Modules\Newsletters\Entities;

//use Shetabit\Shopit\Modules\Newsletters\Entities\Newsletters as BaseNewsletters;

use Illuminate\Database\Eloquent\Model;
//use Modules\Admin\Entities\Admin;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Helpers\Helpers;
//use Spatie\Activitylog\LogOptions;

class Newsletters extends Model
{
    protected $fillable = ['title', 'body','send_at','status'];

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';

    public static function getAvailableStatus(): array
    {
        return [static::STATUS_SUCCESS, static::STATUS_PENDING, static::STATUS_FAIL];
    }
    public function scopeFilters($query)
    {
        return $query;
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        $admin = \Auth::user() ?? Admin::query()->first();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Newsletters')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "خبرنامه ای {$this->title} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

}
