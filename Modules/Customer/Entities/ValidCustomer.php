<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class ValidCustomer extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name','link','description','status' #image
    ];

    protected $hidden = ['media','created_at', 'updated_at'];

    public function scopeSearchKeywords($query)
    {
        return $query->when(request()->filled('keyword'), function ($q) {
            $q->where('name', 'LIKE', '%' . \request('keyword') . '%');
        });
    }
    public function scopeActive($query)
    {
        return $query->where('status',1);
    }

    //start media
//    protected $with = [
//        'media'
//    ];

    public $appends = [
        'image',
    ];


    public function registerMediaCollections() : void
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

    public function addImage($image): bool|\Spatie\MediaLibrary\MediaCollections\Models\Media
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


}
