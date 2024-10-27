<?php

namespace Modules\Blog\Entities;

use Cviebrock\EloquentSluggable\Sluggable;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Comment\Entities\Comment;
use Modules\Comment\Entities\Commentable;
use Modules\Comment\Entities\HasComment;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\HasMorphAuthors;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Product\Entities\Product;
//use Shetabit\Shopit\Modules\Blog\Entities\Post as BasePost;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\HasTags;

class Post extends Model implements Sortable, HasMedia, HasComment, Viewable
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'order',
        'body',
        'meta_description',
        'status',
        'special',
        'published_at',
        'read_time',
    ];


    //    protected static $commonRelations = [
    //        'category', 'tags', 'comments', 'creator'
    //    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'post_product', 'post_id', 'product_id');
    }
    public function countComment(): Int
    {
        return $this->comments()->count();
    }
    public function scopePublished($query)
    {
        $query->where('status', static::STATUS_PUBLISHED)
            ->where('published_at', '<=', now());
    }


    // came from vendor ================================================================================================
    use HasMorphAuthors,
        Sluggable,
        //        HasCommonRelations,
        SortableTrait,
        InteractsWithMedia,
        Commentable,
        HasTags,
        InteractsWithViews
        /*LogsActivity*/;

    const STATUS_DRAFT = 'draft';

    const STATUS_PENDING = 'pending';

    const STATUS_PUBLISHED = 'published';

    const STATUS_UNPUBLISHED = 'unpublished';

    protected  $appends = [/*'image', 'views_count'*/];

    public function getViewsCountAttribute()
    {
        return views($this)->count();
    }

//    protected $hidden = ['media'];
    protected $hidden = ['media','created_at', 'updated_at',"creatorable_type","creatorable_id","updaterable_type","updaterable_id","created_at","updated_at"];





    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $withCount = ['comments'];


    protected $dates = [
        'published_at'
    ];

    protected $allFields = [
        'id',
        'title',
        'slug',
        'summary',
        'order',
        'body',
        'meta_description',
        'status',
        'special',
        'published_at',
        'created_at',
        'updated_at',
        'creatorable_id',
        'updaterable_id'
    ];

    protected $longFields = [
        'body'
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function booted()
    {
        static::deleted(function (\Modules\Blog\Entities\Post $post) {
            $post->tags()->detach();
            $post->comments()->delete();
        });
        Helpers::clearCacheInBooted(static::class, 'home_post');
    }

    //    public function getActivitylogOptions(): LogOptions
    //    {
    //        $admin = \Auth::user();
    //        $name = $admin ? (!is_null($admin->name) ? $admin->name : $admin->username) :
    //            'سیستم';
    //        return LogOptions::defaults()
    //            ->useLogName('Blog')->logAll()->logOnlyDirty()
    //            ->setDescriptionForEvent(function ($eventName) use ($name) {
    //                $eventName = Helpers::setEventNameForLog($eventName);
    //                return "پست {$this->title} توسط ادمین {$name} {$eventName} شد";
    //            });
    //    }


    public static function getAvailableStatuses()
    {
        return [
            static::STATUS_DRAFT,
            static::STATUS_PENDING,
            static::STATUS_PUBLISHED,
            static::STATUS_UNPUBLISHED
        ];
    }

    //Media library

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function addImage($file)
    {
        return $this->addMedia($file)
            ->withCustomProperties(['type' => 'post'])
            ->toMediaCollection('image');
    }

    public function getImageAttribute(): ?MediaDisplay
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('image');
        if (!$media) {
            return null;
        }
        return MediaDisplay::objectCreator($media);
    }

    public function scopeFilters($query)
    {
        return $query
            ->when(request('id'), fn($q) => $q->where('id', request('id')))
            ->when(request('title'), fn($q) => $q->where('title', 'LIKE', '%' . request('title') . '%'))
            ->when(request('post_category_id'), function ($q) {
                if (request('post_category_id') != 'all') {
                    $q->where('post_category_id', request('post_category_id'));
                }
            })
            ->when(request('status'), function ($q) {
                if (request('status') != 'all') {
                    $q->where('status', request('status'));
                }
            })
            ->when(request('start_date'), fn($q) => $q->whereDate('created_at', '>=', request('start_date')))
            ->when(request('end_date'), fn($q) => $q->whereDate('created_at', '<=', request('end_date')));
    }


    //Relations

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function scopeIndex($query, $longFields = null)
    {
        $longFields = $longFields ?? $this->longFields;

        $query->select(array_diff($this->allFields, $longFields));
    }

    public function getStatusColorAttribute(): string
    {
        return config('blog.status_color.' . $this->attributes['status']);
    }
    public function comments()  
    {  
        return $this->hasMany(Comment::class, 'commentable_id')->where('commentable_type', Post::class);  
    } 
    public function isDeletable(): bool
    {
        return true;
    }
}
