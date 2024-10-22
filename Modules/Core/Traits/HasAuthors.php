<?php

namespace Modules\Core\Traits;

//use Shetabit\Shopit\Modules\Core\Traits\HasAuthors as BaseHasAuthors;


use Modules\Admin\Entities\Admin;

trait HasAuthors
{
    public function initializeHasAuthors()
    {
        if (!(isset($this->disableAuthors) && $this->disableAuthors === false)) {
            static::creating(function ($model) {
                $user = \Auth::user() ?? Admin::query()->first();
                $model->creator()->associate($user);
                $model->updater()->associate($user);
            });

            static::updating(function ($model) {
                $user = \Auth::user() ?? Admin::query()->first();
                $model->updater()->associate($user);
            });
        } else {
//            dd('ko');
        }
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(Admin::class, 'updater_id');
    }
}
