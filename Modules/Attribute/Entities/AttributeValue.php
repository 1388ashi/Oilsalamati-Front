<?php

namespace Modules\Attribute\Entities;

use Illuminate\Database\Eloquent\Factories\ry;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Traits\HasAuthors;
use Shetabit\Shopit\Modules\Attribute\Entities\Attribute;
//use Shetabit\Shopit\Modules\Attribute\Entities\AttributeValue as BaseAttributeValue;

class AttributeValue extends Model
{
    use HasAuthors/*, HasCommonRelations*/;

    protected $fillable = [
        'value', 'selected'
    ];


    protected $hidden = ['created_at', 'updated_at','creator_id','updater_id'];

    //Relations

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
