<?php

namespace Modules\Contact\Entities;

use Modules\Customer\Entities\Customer;
//use Shetabit\Shopit\Modules\Contact\Entities\Contact as BaseContact;
use Modules\Core\Entities\BaseModel as Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'subject',
        'body',
        'customer_id',
        'answer',
    ];

    protected $with = [
        'customer'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class)
            ->withOnly(['media'])
            ->select(['id','first_name','last_name','gender','mobile']);
    }
}
