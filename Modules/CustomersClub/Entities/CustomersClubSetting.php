<?php

namespace Modules\CustomersClub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomersClubSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key','value','type','status'];

    protected static function newFactory()
    {
        return \Modules\CustomersClub\Database\factories\CustomersClubSettingsFactory::new();
    }
}
