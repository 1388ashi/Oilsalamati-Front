<?php

namespace Modules\Specification\Entities;

//use Shetabit\Shopit\Modules\Specification\Entities\SpecificationValue as BaseSpecificationValue;

use Illuminate\Database\Eloquent\Model;

class SpecificationValue extends Model
{

    protected $fillable = [
        'value', 'selected'
    ];

    public static function booted()
    {
        static::deleting(function (\Modules\Specification\Entities\SpecificationValue $specificationValue) {
            //check conditions
        });
    }

    //Relations

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }
}
