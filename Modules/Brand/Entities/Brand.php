<?php

namespace Modules\Brand\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Product\Entities\Product;
//use Shetabit\Shopit\Modules\Brand\Entities\Brand as BaseBrand;
use Spatie\MediaLibrary\HasMedia;
use Modules\Core\Services\Media\MediaDisplay;

class Brand extends Model implements HasMedia
{
    use InteractsWithMedia, HasAuthors;

    const COLLECTION_NAME_IMAGES = 'images';
    const COLLECTION_NAME_IMAGES_MOBILE = 'images_mobile';

    protected  $appends = ['image'];
    protected $hidden = ['media'];

    protected $fillable = [
        'name',
        'status',
        'show_index',
        'description',
    ];

    protected $dependantRelations = []; //TODO Product


    protected static function booted()
    {
        static::deleting(function (\Modules\Brand\Entities\Brand $brand){
            if ($brand->products()->exists()) {
                throw Helpers::makeValidationException('به علت وجود محصولی با این برند امکان حذف آن وحود ندارد');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status' , true);
    }

    public function scopeIndexActive($query)
    {
        return $query->where('show_index' , true);
    }

    public function registerMediaCollections() : void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function addImage($file)
    {
        return $this->addMedia($file)
            ->withCustomProperties(['type' => 'brand'])
            ->toMediaCollection('image');
    }

    public function getImageAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('image');
        if (!$media) {
            return null;
        }
        return MediaDisplay::objectCreator($media);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
