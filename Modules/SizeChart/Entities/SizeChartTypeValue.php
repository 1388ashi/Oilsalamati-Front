<?php

namespace Modules\SizeChart\Entities;

//use Modules\SizeChart\Entities\SizeChartType;
//use Shetabit\Shopit\Modules\SizeChart\Entities\SizeChartTypeValue as BaseSizeChartTypeValue;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Modules\Core\Entities\BaseModel;

class SizeChartTypeValue extends Model
{

    protected $fillable = ['name'];

    public function type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SizeChartType::class, 'type_id', 'id');
    }
}
