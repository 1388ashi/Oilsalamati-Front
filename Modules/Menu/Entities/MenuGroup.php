<?php

namespace Modules\Menu\Entities;

//use Shetabit\Shopit\Modules\Menu\Entities\MenuGroup as BaseMenuGroup;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;

class MenuGroup extends Model
{

    protected $fillable = ['title'];
    protected $hidden = ['created_at', 'updated_at'];
}
