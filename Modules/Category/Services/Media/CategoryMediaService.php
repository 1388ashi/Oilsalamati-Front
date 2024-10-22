<?php

namespace Modules\Category\Services\Media;

use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Core\Services\Media\MediaDisplay;

class CategoryMediaService extends CacheServiceInterface
{ // I don't want to use of this class. you can delete it.
    public static array $usedModelsInCache = [Category::class];

    protected function constructNeedId(): bool { return true; }

    public function cacheCreator($id): void
    {
        // make it
        $category = Category::find($id);
        // attention: category has two collection_names in spatie media-library. images and icon. both are single
        $imageMedia = $category->getFirstMedia('images');
        $this->cacheData['images'] = ($imageMedia) ? MediaDisplay::objectCreator($imageMedia) : null;

        $iconMedia = $category->getFirstMedia('icon');
        $this->cacheData['icon'] = ($iconMedia) ? MediaDisplay::objectCreator($iconMedia) : null;
        Cache::forever(self::getCacheName($id), $this->cacheData);
    }

    public function getImageMedia() {
        return $this->cacheData['images'];
    }

    public function getIconMedia() {
        return $this->cacheData['icon'];
    }
}
