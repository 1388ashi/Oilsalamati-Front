<?php


namespace Modules\Link\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Classes\DontAppend;

trait HasLinks
{
    public function initializeHasLinks() {
        $this->mergeFillable(['link', 'linkable_id', 'linkable_type', 'new_tab']);
//        $this->with[] = 'linkable'; ارور میده
        $this->append('slug');
        $this->makeHidden('linkable');
    }

    public function setLink($link)
    {
        if ($link instanceof Model) {
            $this->linkable()->associate($link);
        } elseif (is_string($link)) {
            $this->link = $link;
        }
    }

    public function linkable()
    {
        return $this->morphTo('linkable');
    }

    public function getSlugAttribute()
    {
        return Cache::remember('linkable-model-' . $this->id, 600, function () {
            if (!class_exists($this->linkable_type) || !$this->linkable) {
                return '';
            }
            return $this->linkable->slug;
        });
    }
}
