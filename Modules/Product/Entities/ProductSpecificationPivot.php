<?php

namespace Modules\Product\Entities;

//use Shetabit\Shopit\Modules\Product\Entities\ProductSpecificationPivot as BaseProductSpecificationPivot;


use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Specification\Entities\SpecificationValue;

class ProductSpecificationPivot extends Pivot
{
    protected $table = 'product_specification';
    protected $with = [
        'specificationValues'
    ];

    public function specificationValues(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            SpecificationValue::class,
            'product_specification_specification_value',
            'product_specification_id',
            'specification_value_id'
        );
    }

    public function specificationValue()
    {
        return $this->belongsTo(SpecificationValue::class);
    }
}
