<?php

namespace Modules\Flash\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Entities\Admin;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Cache\CacheForgetService;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Setting;
//use Shetabit\Shopit\Modules\Flash\Entities\Flash as BaseFlash;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use DateTimeInterface;

class Flash extends Model implements Sortable, HasMedia
{
    public const Setting_DefaultFlatAmount = 'flash_default_flat_discount_amount';
    public const Setting_DefaultPercentageAmount = 'flash_default_percentage';

    const COLLECTION_NAME_IMAGES = 'images';
    const COLLECTION_NAME_IMAGES_MOBILE = 'images_mobile';

    public static function getDefaultDiscount($discountType = Flash::DISCOUNT_TYPE_FLAT,$defaultReturn = 0){
        return match($discountType){
            static::DISCOUNT_TYPE_FLAT => (Setting::getFromName(static::Setting_DefaultFlatAmount) ?? $defaultReturn),
            static::DISCOUNT_TYPE_PERCENTAGE => (Setting::getFromName(static::Setting_DefaultPercentageAmount) ?? $defaultReturn)
        };

    }

    public function scopeActive( $query)
    {
        $current_time = date("Y-m-d H:i:s");
        return $query
            ->where('start_date', '<=', $current_time)
            ->where('end_date', '>=', $current_time)
            ->where('status', '=', 1);
    }
    public function scopeFilters($query)
    {
        return $query;
    }

    // came from vendor ================================================================================================
    use HasAuthors, /*LogsActivity,*/
        InteractsWithMedia, SortableTrait, HasDefaultFields;

    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FLAT = 'flat';

    CONST ACCEPTED_IMAGE_MIMES = 'gif|png|jpg|jpeg';

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'preview_count',
        'order',
        'timer',
        'status',
        'color'
    ];

    protected  $appends = [/*'image', 'bg_image', 'mobile_image'*/];

    protected $hidden = ['media'];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $defaults = [
        'preview_count' => 7
    ];

    protected $dates = [
        'start_date', 'end_date'
    ];

    protected static $commonRelations = [
        /*'products'*/
    ];

//    public function getActivitylogOptions(): LogOptions
//    {
//        $admin = \Auth::user();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Flash')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "کمپین {$this->title} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i');
    }

    public static function getAvailableDiscountTypes()
    {
        return [static::DISCOUNT_TYPE_PERCENTAGE, static::DISCOUNT_TYPE_FLAT];
    }

    public static function booted()
    {
        static::deleting(function (\Shetabit\Shopit\Modules\Flash\Entities\Flash $flash) {
            //todo unable delete
            CacheForgetService::run($flash);
        });
        static::updating(function (\Shetabit\Shopit\Modules\Flash\Entities\Flash $flash) {
            CacheForgetService::run($flash);
        });

        Helpers::clearCacheInBooted(static::class, 'home_flash');

    }



    //Media library
    public function registerMediaCollections() : void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('mobile_image')->singleFile();
        $this->addMediaCollection('bg_image')->singleFile();
    }

    public function addImage($file)
    {
        if (!$file) {
            return ;
        }
        return $this->addMedia($file)
            ->withCustomProperties(['type' => 'flash'])
            ->toMediaCollection('image');
    }

    public function addMobileImage($file)
    {
        if (!$file) {
            return ;
        }
        return $this->addMedia($file)
            ->withCustomProperties(['type' => 'flash'])
            ->toMediaCollection('mobile_image');
    }

    public function addBackgroungImage($file)
    {
        return $this->addMedia($file)
            ->withCustomProperties(['type' => 'flash'])
            ->toMediaCollection('bg_image');
    }

    public function getImageAttribute()
    {
        $media = $this->getFirstMedia('image');
        if (!$media) {
            return null;
        }
        return new MediaResource($media);
    }

    public function getMobileImageAttribute()
    {
        $media = $this->getFirstMedia('mobile_image');
        if (!$media) {
            return null;
        }
        return new MediaResource($media);
    }

    public function getBgImageAttribute()
    {
        $media = $this->getFirstMedia('bg_image');
        if (!$media) {
            return null;
        }
        return new MediaResource($media);
    }

    //Relations

    public function products()
    {
        $query = $this->belongsToMany(Product::class)
            ->withPivot(['discount_type', 'discount', 'salable_max', 'sales_count'])
            ->with('varieties');
        if (!(Auth::user() instanceof Admin)) {
            $query->available();
        }
        return $query;
    }

    public function activeProducts()
    {
        return $this->belongsToMany(Product::class)->available(true)
            ->withPivot(['discount_type', 'discount', 'salable_max', 'sales_count'])
            ->whereColumn('sales_count', '<', 'salable_max')
            ->with('varieties');
    }

    public function finalDiscount($price)
    {
        if($this->descount_type == static::DISCOUNT_TYPE_FLAT){
            return $this->discount;
        }

        return (int)round(($this->discount * $price) / 100);
    }
}
