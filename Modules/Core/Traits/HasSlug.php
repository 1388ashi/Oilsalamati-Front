<?php

namespace Modules\Core\Traits;

//use Shetabit\Shopit\Modules\Core\Traits\HasSlug as BaseHasSlug;

trait HasSlug
{
    public function initializeHasSlug()
    {
        $this->append('slug');
    }

    public function getSlugAttribute()
    {
        if (isset($this->slugFrom)) {
            return str_replace(' ', '-', trim($this->{$this->slugFrom}));
        }

        return $this->title;
    }
}
