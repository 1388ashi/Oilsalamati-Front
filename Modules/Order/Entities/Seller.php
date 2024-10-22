<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasAuthors;

class Seller extends Model
{
    use HasAuthors;

    protected $fillable = ['full_name', 'national_code', 'description'];
}
