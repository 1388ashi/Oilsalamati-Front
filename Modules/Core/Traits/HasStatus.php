<?php

namespace Modules\Core\Traits;

//use Shetabit\Shopit\Modules\Core\Traits\HasStatus as BaseHasStatus;


use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Entities\BaseEloquentBuilder;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Scopes\StatusScope;

/**
 * Trait HasStatus
 * @package Modules\Core\Traits
 * @method static BaseEloquentBuilder withDisabled()
 */
trait HasStatus
{

    public static function bootHasStatus()
    {
        static::addGlobalScope(new StatusScope());
        static::updating(function ($model) {
            if ($model->isDirty('status') && $model->status == false && is_array($model->dependantRelations)) {
                foreach ($model->dependantRelations as $dependantRelation) {
                    if ($model->$dependantRelation()->where('status', 1)->exists()) {
                        throw Helpers::makeValidationException('You cannot disable it because it has a dependency: ' . $dependantRelation);
                    }
                }
            }
        });
        static::deleting(function ($model) {
            foreach ($model->dependantRelations as $dependantRelation) {
                if ($model->$dependantRelation()->exists()) {
                    throw Helpers::makeValidationException('You cannot delete it because it has a dependency: ' . $dependantRelation);
                }
            }
        });
    }

    /**
     * @return Builder
     */
    public function scopeWithDisabled($query): Builder
    {
        return $query->withoutGlobalScope(StatusScope::class);
    }
}
