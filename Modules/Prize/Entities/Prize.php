<?php

namespace Modules\Prize\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Customer\Entities\Customer;

class Prize extends Model
{
    protected $fillable = [
        'amount'
    ];

    public function prizable()
    {
        return $this->morphTo('prizable');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function storeModel($prizable, Customer $customer, $amount)
    {
        $prize = new static(['amount' => $amount]);
        $prize->prizable()->associate($prizable);
        $prize->customer()->associate($customer);
        $prize->save();

        return $prize;
    }
}
