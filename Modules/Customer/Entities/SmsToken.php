<?php

namespace Modules\Customer\Entities;

//use Shetabit\Shopit\Modules\Customer\Entities\SmsToken as BaseSmsToken;
use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;

class SmsToken extends Model
{
//    use HasCommonRelations;

    protected $fillable = [
        'mobile', 'token', 'expired_at', 'verified_at'
    ];

    protected $dates = [
        'expired_at', 'verified_at'
    ];


}
