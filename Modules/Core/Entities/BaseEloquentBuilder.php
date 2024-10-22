<?php

namespace Modules\Core\Entities;

use Shetabit\Shopit\Modules\Core\Entities\BaseEloquentBuilder as BaseBaseEloquentBuilder;


use http\Exception\BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Scopes\StatusScope;
use Shetabit\Shopit\Modules\Product\Entities\Product;

class BaseEloquentBuilder extends Builder
{
    public function getRelation($name)
    {
        // We want to run a relationship query without any constrains so that we will
        // not have to remove these where clauses manually which gets really hacky
        // and error prone. We don't want constraints because we add eager ones.
        $relation = Relation::noConstraints(function () use ($name) {
            try {
                return $this->getModel()->newInstance()->$name();
            } catch (BadMethodCallException $e) {
                throw RelationNotFoundException::make($this->getModel(), $name);
            }
        });

        $nested = $this->relationsNestedUnder($name);

        // If there are nested relationships set on the query, we will put those onto
        // the query instances so that they can be handled after this relationship
        // is loaded. In this way they will all trickle down as they are loaded.
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }

        if (!key_exists(StatusScope::class, $this->scopes)) {
            $relation->getQuery()->withoutGlobalScope(StatusScope::class);
        }

        return $relation;
    }

    public function latest($column = null)
    {
        if ($this->model instanceof Product) {
            $this->query->orderBy('published_at', 'desc')->orderBy('id', 'desc');
        }
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->latest($column);

        return $this;
    }
}
