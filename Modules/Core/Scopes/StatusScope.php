<?php

namespace Modules\Core\Scopes;

//use Shetabit\Shopit\Modules\Core\Scopes\StatusScope as BaseStatusScope;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StatusScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (method_exists($model, 'scopeActive')) {
            return $model->scopeActive($builder);
        }

        return $this->isActive($builder, $model);
    }


    public function isActive(Builder $builder, Model $model)
    {
        return $builder->where($model->getTable() . '.status', '=', 1);
    }
}
