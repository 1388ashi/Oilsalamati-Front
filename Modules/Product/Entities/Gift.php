<?php

namespace Modules\Product\Entities;

//use Shetabit\Shopit\Modules\Product\Entities\Gift as BaseGifts;

//use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Media\MediaDisplay;
//use Modules\Core\Transformers\MediaResource;
use Modules\Core\Traits\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
//use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Gift extends Model implements HasMedia
{
    use InteractsWithMedia;

    const COLLECTION_NAME_IMAGES = 'images';
    const COLLECTION_NAME_IMAGES_MOBILE = 'images_mobile';

    protected $fillable= ['name', 'start_date', 'end_date', 'status'];

    protected $appends = ['image'];

    protected $hidden = ['media'];

    protected static function booted()
    {
        parent::booted();
        static::deleting(function (\Modules\Product\Entities\Gift $gift){
            if ($gift->varieties()->exists() || $gift->products()->exists()){
                throw Helpers::makeValidationException('جایزه به تنوع یا محصول وصل شده است');
            }
        });
    }

    public function varieties(): BelongsToMany
    {
        return $this->belongsToMany(Variety::class, 'gift_product_variety')
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Product\Entities\Product::class,
            'gift_product_variety')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        $query->where('status' , true)
            ->whereDate('start_date', '<=', now()->format('Y-m-d H:i'))
            ->whereDate('end_date', '>=', now()->format('Y-m-d H:i'));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }

    public function addImage($file)
    {
        if (!is_file($file)) return;

        $this->addMedia($file)
            ->toMediaCollection('images');

        $this->load('media');
    }

    public function getImageAttribute()
    {
        $media = $this->getFirstMedia('images');

        if (!$media) return null;
        return MediaDisplay::objectCreator($media);
    }

    public function getMorphClass()
    {
        return \Modules\Product\Entities\Gift::class;
    }
}
