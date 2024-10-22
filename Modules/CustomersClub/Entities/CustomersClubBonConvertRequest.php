<?php

namespace Modules\CustomersClub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customer\Entities\Customer;

class CustomersClubBonConvertRequest extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $appends = [
        'customer_name'
    ];

    protected static function newFactory()
    {
        return \Modules\CustomersClub\Database\factories\BonConvertRequestFactory::new();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getCustomerNameAttribute()
    {
        $c = $this->customer;
        return $c?$c->first_name." ".$c->last_name." ({$c->mobile})":'';
    }
}
