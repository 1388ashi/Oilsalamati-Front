<?php

namespace Modules\Menu\Entities;

//use Shetabit\Shopit\Modules\Menu\Entities\MenuItem as BaseMenuItem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Entities\Admin;
//use Modules\Category\Entities\Category;
use Modules\Core\Classes\DontAppend;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Link\Traits\HasLinks;
//use Modules\Menu\Entities\MenuGroup;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;

class MenuItem extends Model implements HasMedia,Sortable
{
    use HasLinks, InteractsWithMedia, SortableTrait;

    protected $defaults = [
      'order' => 1
    ];
    protected $fillable = [
        'linkable_id', 'linkable_type', 'url', 'title', 'parent_id', 'order', 'new_tab', 'status', 'group_id'
    ];
    public $sortable = [
      'order_column_name' => 'order',
      'sort_when_creating' => true,
    ];
    protected $hidden = ['media','created_at','updated_at'];

    protected $appends = [
//        'linkable_type_name',
        'unique_type',
        'icon'
    ];

    public static function booted()
    {
        static::deleting(function (\Modules\Menu\Entities\MenuItem $menuItem) {
            foreach ($menuItem->children as $childMenuItem) {
                $childMenuItem->delete();
            }
        });

        static::updated(function (MenuItem $menuItem) {
            Cache::forget('linkable-model-' . $menuItem->id);
        });

        Helpers::clearCacheInBooted(static::class, 'home_menu');
    }

    public function registerMediaCollections():void
    {
        $this->addMediaCollection('icon')->singleFile();
    }

    public function addIcon($file)
    {
        return $this->addMedia($file)->toMediaCollection('icon');
    }

    public function getIconAttribute(): MediaResource|DontAppend|null
    {
        if (!$this->relationLoaded('media')) {
            return new DontAppend('getIconAttribute');
        }
        $media = $this->getFirstMedia('icon');
        if (!$media) {
            return null;
        }
        return new MediaResource($media);
    }

    public function group()
    {
        return $this->belongsTo(MenuGroup::class, 'group_id');
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id', 'id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $q =  $this->hasMany(static::class, 'parent_id', 'id')
            ->orderBy('order', 'desc')->with('children');
        if (!(Auth::user() instanceof Admin)) {
            $q->active();
        }

        return $q;
    }

    public function scopeActive($query)
    {
        $query->where('status', true);
    }

    public static function scopeIsParent($query)
    {
        $query->whereNull('parent_id');
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

    public static function sort(array $menuItems, MenuGroup $menuGroup, $parentId = null)
    {
        $order = 999999;
        foreach ($menuItems as $menuItemId) {
            $menuItem = static::where('group_id', $menuGroup->id)->find($menuItemId['id']);
            if (!$menuItem) continue;
            $menuItem->order = $order--;
            $menuItem->parent_id = $parentId;
            $menuItem->save();
            if (isset($menuItemId['children'])) {
                static::sort($menuItemId['children'], $menuGroup, $menuItem->id);
            }
        }
    }
}
