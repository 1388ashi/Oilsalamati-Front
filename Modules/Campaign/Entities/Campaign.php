<?php

namespace Modules\Campaign\Entities;

use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Exceptions\ModelCannotBeDeletedException;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Campaign extends Model implements HasMedia,Viewable
{
    use InteractsWithMedia,InteractsWithViews;

    protected $fillable = [
        'title','start_date','end_date','status',
        'customer_title','customer_text','coupon_code',
    ];

    protected $appends = [
        'file','file_path','views_count',
    ];

    #TODO : MEDIA(VIDEO)

    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_OPTION = 'options';
    const TYPE_TEXT = 'text';

    public static function getAvailableTypes()
    {
        return [
            static::TYPE_CHECKBOX,
            static::TYPE_OPTION,
            static::TYPE_TEXT,
        ];
    }

    public static function getQualityLabelAttribute($type): string
    {
        $t = [
            static::TYPE_CHECKBOX => 'بله/خیر',
            static::TYPE_OPTION => 'گزینه ای',
            static::TYPE_TEXT => 'تکست',
        ];

        return $t[$type];
    }


    public function scopeActive($query)
    {
        return $query->where('status',1);
    }


    public function scopeSearchKeywords($query)
    {
        return $query->when(request()->filled('keyword'), function ($q) {
            $q->where('title', 'LIKE', '%' . \request('keyword') . '%')
                ->orWhere('customer_title','LIKE','%'.\request('keyword').'%')
                ->orWhere('customer_text','LIKE','%'.\request('keyword').'%')
            ;
        });
    }

    public function scopeSearchBetweenTwoDate($query)
    {
        $startDate = \request('from_date');
        $endDate = \request('to_date');

        return $query
            ->when($startDate & $endDate, function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('created_at', [$startDate, $endDate]);
            });
    }


    public function isDeletable(): bool
    {
        if ($this->users->count()){
            return false;
        }

        return true;
    }

    public static function booted()
    {
        static::deleting(function (Campaign $campaign) {
            if ($campaign->users()->count() > 0) {
                throw new ModelCannotBeDeletedException('این جشنواره دارای کاربر است و قابل حذف نمیباشد.');
            }
        });
    }

    //start spatie media[file]
    protected $with = [
        'media'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')->singleFile();
    }

    public function getFileAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('files');
        if (!$media) {
            return null;
        }
        return MediaDisplay::objectCreator($media);
    }

    public function getFilePathAttribute(): ?string
    {
        $media = $this->getFirstMedia('files');
        if (!$media) {
            return null;
        }
        return $media->getPath();
    }

    public function addFile($file): bool|\Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        if (!$file) {
            return false;
        }

        return $this->addMedia($file)->toMediaCollection('files');
    }


    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file') && $request->file('file')) {
            $this->addFile($request->file('file'));
        }
    }

    public function deleteFile()
    {
        $this->media()->delete();
    }

    //end spatie media


    public function getViewsCountAttribute()
    {
        return views($this)->unique()->count();
    }

    public function questions()
    {
        return $this->hasMany(CampaignQuestion::class);
    }

    public function users()
    {
        return $this->hasMany(CampaignUser::class);
    }
}
