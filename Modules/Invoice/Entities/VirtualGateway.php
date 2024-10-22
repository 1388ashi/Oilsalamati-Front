<?php

namespace Modules\Invoice\Entities;

//use Shetabit\Shopit\Modules\Invoice\Entities\VirtualGateway as BaseVirtualGateway;

use Illuminate\Database\Eloquent\Model;

class VirtualGateway extends Model
{
    protected $fillable = [
        'amount', 'callback', 'transaction_id'
    ];
}
