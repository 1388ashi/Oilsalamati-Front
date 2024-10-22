<?php

namespace Modules\SizeChart\Entities;

//use Shetabit\Shopit\Modules\SizeChart\Entities\SizeChartType as BaseSizeChartType;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasAuthors;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Entities\SizeChartTypeValue;
use Modules\Core\Helpers\Helpers;

class SizeChartType extends Model
{
    use HasAuthors;

    protected $fillable = ['name'];

    protected $with = ['values'];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($item){
            /** @var $item static */
            if($item->sizeChart()->exists()){
                throw Helpers::makeValidationException('این نوع سایزچارت قابل حذف نمی باشد');
            }
        });
    }

    public function sizeChart(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SizeChart::class, 'type_id');
    }

    public function values(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SizeChartTypeValue::class, 'type_id');
    }

    public function storeModel(Request $request): Model|\Illuminate\Database\Eloquent\Builder
    {
        return static::query()->create($request->all());
    }
}
