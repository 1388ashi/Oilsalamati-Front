<?php

namespace Modules\Core\Services\Cache;

use Modules\Category\Services\CategoriesCollectionService;
use Modules\Category\Services\Media\CategoryMediaService;
use Modules\Flash\Services\FlashMediaService;
use Modules\Home\Services\HomeService;
use Modules\Product\Services\ProductDetailsService;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Shipping\Services\ShippingCollectionService;
use Modules\Store\Services\StoreBalanceService;

class CacheForgetService
{
    private static array $servicesList = [
        StoreBalanceService::class,
        CategoryMediaService::class,
        FlashMediaService::class,
        ProductsCollectionService::class,
        HomeService::class,
        CategoriesCollectionService::class,
        ProductDetailsService::class,
        ShippingCollectionService::class
    ];

    public static function run($model)
    {
        foreach (self::$servicesList as $service) {
            if (in_array($model, $service::getUsedModelsInCache())) {
                $service::forgetCache($model->id);
            }
        }

    }

}
