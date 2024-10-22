<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class ChargeType extends Model
{
    protected $fillable = ['title','value','is_gift'];


    public static function getChargeTypeIdByValue($value)
    {
        if (!Cache::has('AllChargeTypes')) {
            // create it
            $cache_file = ChargeType::all();
            Cache::put('AllChargeTypes', $cache_file, now()->addHour(1));
        } else {
            $cache_file = Cache::get('AllChargeTypes');
        }

        return $cache_file->where('value', $value)->first()->id;
    }
}
