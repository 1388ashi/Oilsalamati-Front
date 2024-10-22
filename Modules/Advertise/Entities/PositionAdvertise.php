<?php

namespace Modules\Advertise\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Advertise\Entities\PositionAdvertise as BasePositionAdvertise;

class PositionAdvertise extends Model
{
    protected $fillable = ['width', 'height', 'label', 'key', 'description', 'status'];

    protected $table = 'advertisement_positions';

    public static function booted()
    {
        Helpers::clearCacheInBooted(static::class, 'home_advertise');
    }

    public function advertisements()
    {
        return $this->hasMany(Advertise::class, 'position_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status' , true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('end' , '<' , Carbon::now());
    }
}
