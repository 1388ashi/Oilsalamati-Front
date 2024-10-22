<?php

namespace Modules\Report\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attribute\Entities\Attribute;
use Modules\Product\Entities\VarietyAttributeValuePivot;
use Shetabit\Shopit\Modules\Report\Entities\VarietyReport as BaseVarietyReport;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Shetabit\Shopit\Modules\Core\Entities\BaseModel;

class VarietyReport extends Variety
{
    protected $table = 'variety_reports_view';

    public function getMorphClass()
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array(Variety::class, $morphMap)) {
            return array_search(Variety::class, $morphMap, true);
        }

        return Variety::class;
    }
    public function scopeFilters($query)
    {
        $name = request('name');
        $startDate = request('start_date');
        $endDate = request('end_date');
        return $query
            ->when($startDate && $endDate, function (Builder $query) use ($startDate, $endDate) {
                $query->whereHas('product', function (Builder $query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
            })
            ->when($name, function (Builder $query) use ($name) {
                $query->whereHas('product', function (Builder $builder) use ($name) {
                    $builder->where('title', 'like', "%$name%");
                });
            });
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_variety', 'variety_id')
            ->using(VarietyAttributeValuePivot::class)
            ->withPivot('attribute_value_id' , 'value');
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
