<?php

namespace Modules\Category\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Entities\Admin;
use Modules\Attribute\Entities\Attribute;
use Modules\Brand\Entities\Brand;
//use Modules\Core\Classes\DontAppend;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Cache\CacheForgetService;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Core\Traits\HasViews;
use Modules\Core\Traits\InteractsWithMedia;
//use Modules\Core\Transformers\MediaResource;
use Modules\Coupon\Entities\Coupon;
use Modules\Product\Entities\Product;
use Modules\Specification\Entities\Specification;
//use Shetabit\Shopit\Modules\Category\Entities\Category as BaseCategory;
use Shetabit\Shopit\Modules\Core\Contracts\HasParent;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;

class Category extends Model implements HasMedia, HasParent
{


    protected $fillable = [
        'title',
        'en_title',
        'description',
        'parent_id',
        'status',
        'special',
        'meta_title',
        'meta_description',
        'priority',
        'level',
        'show_in_home',
        // 'banner_link'
    ];

    public function coupons()
    {
        $this->belongsToMany(Coupon::class,'coupon_categories', 'category_id', 'coupon_id');
    }




    // came from vendor ================================================================================================

    use InteractsWithMedia, HasDefaultFields, HasAuthors/*, LogsActivity*/,
        SortableTrait, HasCommonRelations, HasViews;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $defaults = [
        'special' => true,
        'status' => true,
        'priority' => 1,
        'level' => 1
    ];

    protected $cast = ['priority' => 'int' ];

//    protected $appends = ['image', 'icon'];

    protected $hidden = ['media','created_at', 'updated_at','creator_id','updater_id'];

    protected $with = ['children'];

    protected static $commonRelations = [/*'children'*//*, 'attributes', 'specifications', 'brands', 'products'*/];


    protected static function booted()
    {
        parent::booted();
        static::updating(function ($category){
            CacheForgetService::run($category);
        });

        static::deleting(function ($category){
            if ($category->products()->exists())
                throw Helpers::makeValidationException('دسته بندی دارای محصول میباشد.');
            CacheForgetService::run($category);
        });
        Helpers::clearCacheInBooted(static::class, 'home_category');
        Helpers::clearCacheInBooted(static::class, 'home_special_category');
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        if (app()->runningInConsole()){
//            return LogOptions::defaults()->submitEmptyLogs();
//        }
//        $admin = \Auth::user();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Category')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "دسته بندی {$this->title} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Category\Entities\Category::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class , 'parent_id' , 'id')
            ->orderBy('priority', 'DESC')
            ->with(['children', 'attributes.values', 'brands', 'specifications.values']);
    }

    public function attributes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)->withTimestamps();
    }

    public function specifications(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Specification::class)->withTimestamps();
    }

    public function brands(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Brand::class)->withTimestamps();
    }

    public function scopeParents($query , $parent_id = null)
    {
        return $query->where('parent_id' , $parent_id);
    }

    public function scopeSpecial($query)
    {
        return $query->where('special' , true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public function scopeFilters($query)
    {
        return $query;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
        $this->addMediaCollection('icons')->singleFile();
    }

    public function addImage($file)
    {
        $this->addMedia($file)
            ->withCustomProperties(['type' => 'category'])
            ->toMediaCollection('images');
    }

    public function addIcon($file)
    {
        $this->addMedia($file)
            ->withCustomProperties(['type' => 'category'])
            ->toMediaCollection('icons');
    }


    public function getImageAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('images');
        if (!$media) {
            return null;
        }
        return MediaDisplay::objectCreator($media);
    }

    public function getIconAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('icons');
        if (!$media) {
            return null;
        }
        return MediaDisplay::objectCreator($media);
    }


    public function products()
    {
        $query = $this->belongsToMany(Product::class);
        if (!(Auth::user() instanceof Admin)) {
            $query->active();
        }
        return $query;
    }
    public static function sort(array $categoryIds, $parentId = null)
    {
        $order = 999999;
        foreach ($categoryIds as $categoryId) {
            $category = Category::find($categoryId);
            $category->parent_id = $parentId;
            if (!$category) continue;
            $category->priority = $order--;
            $category->save();
            if (isset($categoryId['children'])) {
                static::sort($categoryId['children'], $category->id);
            }
        }
    }
}
