<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Area\Entities\City;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Traits\HasAuthors;
use Modules\Order\Entities\Order;
//use Shetabit\Shopit\Modules\Customer\Entities\Address as BaseAddress;

class Address extends Model
{
    use HasAuthors/*, HasCommonRelations*/;

    protected $fillable = [
        'city_id',
        'customer_id',
        'first_name',
        'last_name',
        'mobile',
        'address',
        'postal_code',
        'telephone',
        'latitude',
        'longitude'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $with = ['city.province'];


    //Relations

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
