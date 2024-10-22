<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Shipping\Entities\Shipping;

class CustomerRole extends Model
{
    protected $fillable = ['see_expired', 'name'];

    public function customers()
    {
        return $this->hasMany(\Modules\Customer\Entities\Customer::class);
    }

    public function shippings()
    {
        return $this->belongsToMany(Shipping::class)
            ->withPivot(['amount']);
    }
}
