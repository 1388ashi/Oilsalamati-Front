<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendingMessage extends Model
{
    protected $fillable = ['template','mobile','hold_to','token','token2','token3','token10','token20'];
}
