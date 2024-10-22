<?php

namespace Modules\Campaign\Entities;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Exceptions\ModelCannotBeDeletedException;

class CampaignUser extends Model
{
    protected $fillable = [
        'mobile',
        'campaign_id',
    ];


    public function isDeletable(): bool
    {
        if ($this->answers->count()){
            return false;
        }

        return true;
    }

    public static function booted()
    {
        static::deleting(function (CampaignUser $user) {
            if ($user->answers()->count() > 0) {
                throw new ModelCannotBeDeletedException('این کاربر دارای جواب است و قابل حذف نمیباشد.');
            }
        });
    }
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function answers()
    {
        return $this->hasMany(CampaignUserAnswer::class,'user_id');
    }

}
