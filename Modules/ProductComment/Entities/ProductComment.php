<?php

namespace Modules\ProductComment\Entities;


use Illuminate\Database\Eloquent\Model;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductComment extends Model
{

    protected $fillable = [
        'title',
        'body',
        'rate',
        'show_customer_name', //وضعیت نمایش نام کاربر در کامنت گذاشته شده
        'status',
    ];

    //    protected static $commonRelations = ['creator', 'product'];

    protected static $recordEvents = ['deleted'];

    protected $hidden = ['created_at', 'updated_at'];

    const STATUS_APPROVED = 'approved';
    const STATUS_PENDING = 'pending';
    const STATUS_REJECT = 'reject';

    protected $appends = ['creator_fullname'];

    protected static function booted()
    {
        parent::booted();

        self::deleting(function ($comment) {
            if ((auth()->user() instanceof Customer) && $comment->creator_id != auth()->user()->id) {
                throw Helpers::makeValidationException('شما مجاز به حذف این دیدگاه نمی باشید.');
            }
        });
    }

    #Rlations
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    #End Relations

    #Scopes
    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }
    #End Scopes

    #Other Function
    public static function getAvailableStatus(): array
    {
        return [self::STATUS_APPROVED, self::STATUS_PENDING, self::STATUS_REJECT];
    }
    public function getDescriptionForEvent(string $eventName): string
    {
        return "{$eventName} Product comment by " .
            auth()->user()->first_name . " " . auth()->user()->first_name;
    }

    public function scopeFilters($query)
    {
        return $query
            ->when(request('product_id'), fn ($q) => $q->where('product_id', request('product_id')))
            ->when(request('status'), function ($q) {
                if (request('status') != 'all') {
                    $q->where('status', request('status'));
                }
            })
            ->when(request('start_date'), fn($q) => $q->whereDate('created_at', '>=', request('start_date')))
            ->when(request('end_date'), fn($q) => $q->whereDate('created_at', '<=', request('end_date')));
    }

    public function getCreatorFullnameAttribute(): string{
        $customer = $this->creator;
        $this->unsetRelation('creator');
        if (!$customer) return '';

        $fullName = $customer->first_name . ' ' . $customer->last_name;
        if (!$customer->first_name || $customer->last_name) {
            $firstAddress = $customer->addresses()->orderBy('id', 'asc')->first();
            if (!$firstAddress) return '';
            $fullName = $firstAddress->first_name . ' ' . $firstAddress->last_name;
        }
        return $fullName;
    }
}
