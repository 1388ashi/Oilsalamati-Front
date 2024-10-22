<?php

namespace Modules\Core\Traits;

use Modules\Core\Traits\HasAuthors;
//use Shetabit\Shopit\Modules\Core\Traits\HasMorphAuthors as BaseHasMorphAuthors;

use Modules\Admin\Entities\Admin;

trait HasMorphAuthors
{
    use HasAuthors;

    public function creator()
    {
        return $this->morphTo('creatorable');
    }

    public function updater()
    {
        return $this->morphTo('updaterable');
    }
}
