<?php

namespace Modules\Core\Classes;

//use Shetabit\Shopit\Modules\Core\Classes\Transaction as BaseTransaction;

use Bavix\Wallet\Models\Transfer;
use Modules\Customer\Entities\Deposit;

class Transaction extends \Bavix\Wallet\Models\Transaction
{
    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'withdraw_id');
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class);
    }

    public function scopeFilters($query)
    {
        return $query
            ->when(!is_null(request('confirmed')), fn($q) => $q->where('confirmed', request('confirmed')))
            ->when(request('type'), fn($q) => $q->where('type', request('type')))
            ->when(request('start_date'), fn($q) => $q->whereDate('created_at', '>=', request('start_date')))
            ->when(request('end_date'), fn($q) => $q->whereDate('created_at', '<=', request('end_date')))
            ->when(request('customer_id'), fn($q) => $q->where('payable_id', request('customer_id')));
    }
}
