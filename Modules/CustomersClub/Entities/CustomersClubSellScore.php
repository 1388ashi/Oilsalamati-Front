<?php

namespace Modules\CustomersClub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomersClubSellScore extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\CustomersClub\Database\factories\CustomersClubSellScoreFactory::new();
    }
}
