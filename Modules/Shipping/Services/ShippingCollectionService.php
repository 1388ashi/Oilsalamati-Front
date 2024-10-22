<?php

namespace Modules\Shipping\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Entities\ShippingRange;

class ShippingCollectionService extends CacheServiceInterface
{
    public static array $usedModelsInCache = [
        Shipping::class,
        ShippingRange::class
    ];

    protected function constructNeedId(): bool { return false; }

    public function cacheCreator($model_id): void
    {
        $this->cacheData['active'] = Shipping::query()
            ->active()
            ->with('shippingRanges')
            ->get();

        $this->cacheData['notActive'] = Shipping::query()
            ->where('status', '=',false)
            ->get();

        Cache::forever(self::getCacheName(), $this->cacheData);
    }


    public function getActiveShippings() {
        return $this->cacheData['active'];
    }

    public function getShippableShippingsForAddress($address)
    {
        $suitableShippings = [];
        $allShippings = $this->cacheData['active'];
        foreach ($allShippings as $shipping) {
            if ($shipping->checkShippableAddress($address->city))
                $suitableShippings[] = $shipping;
            $address->unsetRelation('city');
        }

        foreach ($suitableShippings as $suitableShipping) {
            $suitableShipping->makeHidden(['cities','provinces','shippingRanges']);
        }
        return $suitableShippings;
    }
}
