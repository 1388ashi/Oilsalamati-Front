<?php

namespace Modules\Unit\Entities;

//use Shetabit\Shopit\Modules\Unit\Entities\Unit as BaseUnit;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Traits\HasAuthors;

class Unit extends Model
{
    use HasAuthors /*HasCommonRelations,*/ ;

    const PRECISION = ['1', '0.1', '0.01'];

    protected $fillable = [
        'name', 'symbol', 'precision', 'status'
    ];

    protected $hidden = ['created_at', 'updated_at', 'creator_id', 'updater_id'];

    protected static $commonRelations = [
        //'products'
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    public static function booted()
    {
        static::deleting(function (\Modules\Unit\Entities\Unit $unit) {
            //check conditions
        });
    }


    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
