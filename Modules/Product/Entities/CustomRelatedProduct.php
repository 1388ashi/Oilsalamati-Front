<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;

class CustomRelatedProduct extends Model
{

    protected $fillable = [
        'product_id',
        'related_id',
    ];

    protected $appends = [
        'product_title',
        'related_title',
    ];

    public function getProductTitleAttribute()
    {
        return Product::query()->where('id',$this->product_id)->firstOrFail()->title;
    }


    public function getRelatedTitleAttribute()
    {
        return Product::query()->where('id',$this->related_id)->firstOrFail()->title;
    }

}
