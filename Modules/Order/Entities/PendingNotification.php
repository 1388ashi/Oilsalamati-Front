<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendingNotification extends Model
{
    protected $fillable = ['notification_title','model_id','hold_to'];
}
