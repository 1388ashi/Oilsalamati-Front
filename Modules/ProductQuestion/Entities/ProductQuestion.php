<?php

namespace Modules\ProductQuestion\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Entities\Admin;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductQuestion extends Model
{
//    use LogsActivity;

    protected $fillable = [
        'title',
        'body',
        'status',
    ];

//    protected static $commonRelations = ['admin', 'customer', 'product'];

    protected static $recordEvents = ['deleted'];

    const STATUS_APPROVED = 'approved';
    const STATUS_PENDING = 'pending';
    const STATUS_REJECTED = 'rejected';

    public static $statusTexts = [
        self::STATUS_PENDING => 'در انتظار بررسی',
        self::STATUS_APPROVED => 'تأیید شده',
        self::STATUS_REJECTED => 'رد شده'
    ];
    public function getStatusTextAttribute()
    {
        return self::$statusTexts[$this->status];
    }

    protected static function booted()
    {
        parent::booted();

        self::deleting(function($question){
            if ((auth()->user() instanceof Customer) && $question->creator_id != auth()->user()->id){
                throw Helpers::makeValidationException('شما مجاز به حذف این دیدگاه نمی باشید.');
            }
        });
    }

    #Rlations
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductQuestion::class,'parent_id');
    }

    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductQuestion::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
	{
		return $this->hasMany(ProductQuestion::class, 'parent_id');
	}
    #End Relations

    #Scopes
    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }
    public function scopeMainQuestion($query)
    {
        $query->where('parent_id', 0)->orWhere('parent_id', null);
    }
    #End Scopes

    #Other Function
    public static function getAvailableStatus(): array
    {
        return [self::STATUS_APPROVED, self::STATUS_PENDING, self::STATUS_REJECTED];
    }
    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} Product question by ".
            auth()->user()->first_name." ".auth()->user()->first_name;
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        return
//            LogOptions::defaults()->logOnly(['title','body', 'status', 'creator']);
//    }
}
