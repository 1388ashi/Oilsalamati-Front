<?php

namespace Modules\Flash\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Flash\Entities\Flash;

class FlashMediaService extends CacheServiceInterface
{
    public static array $usedModelsInCache = [Flash::class];
    protected function constructNeedId(): bool { return true; }

    public function cacheCreator($model_id):void
    {
        $flash = Flash::find($model_id);
        $imageMedia = $flash->getFirstMedia('image');
        $mobileImageMedia = $flash->getFirstMedia('mobile_image');
        $bgImageMedia = $flash->getFirstMedia('bg_image');

        $this->cacheData = [
            'image' => ($imageMedia) ? MediaDisplay::objectCreator($imageMedia) : null,
            'mobile_image' => ($mobileImageMedia) ? MediaDisplay::objectCreator($mobileImageMedia) : null,
            'bg_image' => ($bgImageMedia) ? MediaDisplay::objectCreator($bgImageMedia) : null
        ];

        Cache::forever(self::getCacheName($model_id), $this->cacheData);
    }

    public function getImageMedia() {
        return $this->cacheData['image'];
    }

    public function getMobileImageMedia() {
        return $this->cacheData['mobile_image'];
    }
    public function getBgImageMedia() {
        return $this->cacheData['bg_image'];
    }

}
