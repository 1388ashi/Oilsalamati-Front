<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Customer\Entities\Customer;
use Modules\Menu\Entities\MenuItem;
use Spatie\MediaLibrary\HasMedia;

class OrderGiftRange extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'price',
        'min_order_amount',
        #image
    ];

    protected $appends = [
        'image'
    ];

    //start media
    protected $with = [
        'media',
    ];

//    protected $hidden = ['media'];

    public function IsBuyable($item)
    {

    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }

    public function getImageAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('images');
        if (!$media) {
            return asset('dist/img/default_image.jpg');
        }
        return MediaDisplay::objectCreator($media);
    }

    public function addImage($image)
    {
        if (!$image) {
            return false;
        }

        return $this->addMedia($image)->toMediaCollection('images');
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image') && $request->file('image')) {
            $this->addImage($request->file('image'));
        }
    }

    public function deleteImage()
    {
        $this->media()->delete();
    }
    //end media

    public function scopeActive($query)
    {
        return $query->where('status',1);
    }


    public static function booted()
    {
        static::deleting(function (OrderGiftRange $range) {
            if ($range->orders->count()){
                return false;
            }

            return true;
        });

    }
    public function isDeletable(): bool
    {
        if ($this->orders->count()){
            return false;
        }

        return true;
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'gift_range_id');
    }




}
