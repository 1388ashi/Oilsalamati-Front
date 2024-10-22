<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Core\Traits\InteractsWithMedia;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class Recommendation extends Model implements HasMedia, Sortable
{
    use HasDefaultFields, SortableTrait, InteractsWithMedia;

    protected $defaults = ['priority' => 1];
    public $sortable = ['order_column_name' => 'priority', 'sort_when_creating' => true];
    protected $fillable = ['group_name', 'title', 'priority', 'link', 'linkable_id', 'linkable_type'];

    protected $hidden = ['created_at', 'updated_at'];

    const COLLECTION_NAME_IMAGES = 'images';
    const COLLECTION_NAME_IMAGES_MOBILE = 'images_mobile';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_NAME_IMAGES);
        $this->addMediaCollection(self::COLLECTION_NAME_IMAGES_MOBILE);
    }

    public function getImagesShowcaseAttribute()
    {
        $mediaDisplayObjects = [];
        $mediaCollections = [self::COLLECTION_NAME_IMAGES, self::COLLECTION_NAME_IMAGES_MOBILE];

        foreach ($mediaCollections as $collection) {
            $allMedias = $this->getMedia($collection);
            foreach ($allMedias as $media) {
                $mediaDisplayObjects[$collection][] = MediaDisplay::objectCreator($media);
            }
        }

        return $mediaDisplayObjects;
    }
    public function recommendationItems(): HasMany
    {
        return $this->hasMany(RecommendationItem::class, 'recommendation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', true);
    }
}
