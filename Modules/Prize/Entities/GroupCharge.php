<?php

namespace Modules\Prize\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
//use Modules\Prize\Entities\Prize;

class GroupCharge extends Model
{
    protected $fillable = [
        'start_date', 'end_date', 'amount'
    ];

    public function prizes()
    {
        return $this->morphMany(Prize::class, 'prizable');
    }
}
