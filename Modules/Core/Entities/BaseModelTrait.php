<?php

namespace Modules\Core\Entities;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Core\Classes\DontAppend;
use Modules\Core\Services\QueryFiltersService;
use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Entities\HasFilters;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BaseModel
 * @package Modules\Core\Entities
 * @method static create($attributes)
 * @method static findOrFail($id)
 * @method static find($id)
 * @method static Builder dateFilter()
 * @method static Builder sortFilter()
 * @method static Builder searchFilters()
 * @method static Builder filters()
 * @property array  withCommonRelations()
 * @property @protected @static array  $commonRelations; // should be static
 */
trait BaseModelTrait
{
    use HasFilters/*, HasCommonRelations*/;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if ($this instanceof HasMedia) {
            $this->with[] = 'media';
        }
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function unsetFillable(...$keys)
    {
        $fillable = $this->fillable;
        foreach ($keys as $key) {
            unset($fillable[$key]);
        }
        $this->fillable($fillable);
    }

    public function getQueryFilterService($query): QueryFiltersService
    {
        return $this->queryFilterService ?? new QueryFiltersService($query, $this->getTable());
    }

    public function scopeDateFilter($query)
    {
        return $this->getQueryFilterService($query)->dateFilter();
    }

    public function scopeSortFilter($query)
    {
        return $this->getQueryFilterService($query)->sortFilter();
    }

    public function scopeSearchFilters($query)
    {
        return $this->getQueryFilterService($query)->searchFilters();
    }

    public function scopeFilters($query)
    {
        $this->scopeDateFilter($query);
        $this->scopeSortFilter($query);

        return $this->scopeSearchFilters($query);
    }

    public function scopePaginateOrAll($query, $perPage = 10, $columns = ['*'])
    {
        $perPage = request('per_page', $perPage);

        return request('all') || \request()->header('accept') == 'x-xlsx' ? $query->get($columns) : $query->paginate($perPage, $columns);
    }

    public function newEloquentBuilder($query)
    {
        return new \Modules\Core\Entities\BaseEloquentBuilder($query);
    }

    public function attributesToArray()
    {
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableAttributes()
        );

        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Next we will handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        // Here we will grab all of the appended, calculated attributes to this model
        // as these attributes are not really in the attributes array, but are run
        // when we need to array or JSON the model for convenience to the coder.
        foreach ($this->getArrayableAppends() as $key) {
            $temp = $this->mutateAttributeForArray($key, null);
            if (!($temp instanceof DontAppend)) {
                $attributes[$key] = $temp;
            }
        }

        return $attributes;
    }

    /*
     *
     * product->relationLoaded('varieties.color')
     */
    public function relationLoaded($key)
    {
        $relations = explode('.', $key);
        if (count($relations) === 1) {
            return array_key_exists($key, $this->relations);
        }

        if (!array_key_exists($relations[0], $this->getRelations())) {
            return false;
        }

        $relation = $this->{$relations[0]};
        if ($relation instanceof Collection) {
            if ($relation->count() === 0) {
                return false;
            }
            unset($relations[0]);
            $relation = $relation->first();
        }

        return $relation->relationLoaded(implode('.', $relations));
    }
}
