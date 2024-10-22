<?php

namespace Modules\Slider\Entities;

//use Shetabit\Shopit\Modules\Slider\Entities\Slider as BaseSlider;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Transformers\MediaResource;
use Modules\Link\Traits\HasLinks;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Modules\Core\Traits\InteractsWithMedia;

class Slider extends Model implements HasMedia,Sortable
{
    use InteractsWithMedia, HasLinks, SortableTrait, HasAuthors;
    protected $defaults = [
      'order' => 1
    ];
    protected $with = [
        'media'
    ];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $casts = ['status' => 'boolean'];

    protected $appends = ['group_label', 'image', 'unique_type'];

    protected $fillable = [
        'title', 'description', 'group', 'link', 'status', 'custom_fields','order','discount'
    ];

    protected $hidden = ['media','created_at','updated_at','creator_id','updater_id'];

    protected static function booted()
    {
        parent::booted();

        Helpers::clearCacheInBooted(static::class, 'home_slider');
    }

    public function getGroupLabelAttribute()
    {
        return __('core::groups.' . $this->group);
    }

    public function registerMediaCollections() : void
    {
        $this->addMediaCollection('main')->singleFile();
    }

    public function getImageAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('main');
        return MediaDisplay::objectCreator($media);
//        return new MediaResource($media);
    }

    public function addImage($image)
    {
        if (!$image) {
            return;
        }

        return $this->addMedia($image)->toMediaCollection('main');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getUniqueTypeAttribute()
    {
        if (!$this->linkable_type) {
            return 'link_url';
        }
        if ($this->linkable_id) {
            return $this->linkable_type;
        } else {
            return 'Index' . $this->linkable_type;
        }
    }
}

