<?php

namespace Modules\Store\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Product\Entities\Variety;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Store\Entities\Store;
use Modules\Store\Entities\StoreTransaction;


/*
 * this service has been created to read all stores from cache.
 *
 * */

class StoreBalanceService extends CacheServiceInterface
{
    protected function constructNeedId(): bool { return false; }
    protected $store;

    public function __construct(protected $variety_id)
    {
        if ($this->variety_id && !(new ProductsCollectionService())->isVarietyIdActive($variety_id))
            throw Helpers::makeValidationException('تنوع حذف شده است');

        parent::__construct();

        $this->store = $this->getStore($this->variety_id);
    }

    public function cacheCreator($variety_id): void
    {
        $this->cacheData = Store::query()->select(['id', 'variety_id', 'balance'])->get();
        Cache::forever(self::getCacheName(), $this->cacheData);
    }

    private function getStore($variety_id) :Store
    {
//        $stores = $this->cacheData;
        /* @var $store Store */
        $store = $this->cacheData->where('variety_id', $variety_id)->first();
        if (!$store) {
            $store = Store::create(['balance' => 0, 'variety_id'=>$variety_id]);
        }
        return $store;
    }

    public function checkBalance($quantity, $wantError = false): bool
    {
        if ($this->store->balance < $quantity) {
            if ($wantError) throw Helpers::makeValidationException('موجودی کافی نیست');
            else return false;
        }
        return true;
    }

    public function getBalance(): int
    {
        return (int)$this->store->balance;
    }

    public function sendToStore($quantity, $description, $order_id = null, $mini_order_id = null)
    {
        /** @var $store Store */
        $store = $this->store;

        $transaction = StoreTransaction::create([
            "type" => Store::TYPE_INCREMENT,
            "description" => $description,
            "quantity" => $quantity,
            'order_id' => $order_id,
            'store_id' => $store->id,
            // we don't store mini_order_id in Benedito project. but it might to exists in other projects
//            'mini_order_id' => $mini_order_id,
        ]);

        $store->increment('balance', $transaction->quantity);
        //example $store->increment('balance', $transaction->quantity);
        //example $store->decrement('balance', $transaction->quantity);
        $this->updateStoreBalanceInCache($store->id, $store->balance);
        return $store;
    }

    public function getFromStore($quantity, $description, $order_id = null, $mini_order_id = null)
    {
        $this->checkBalance($quantity, true);

        /** @var $store Store */
        $store = $this->store;

        $transaction = StoreTransaction::create([
            "type" => Store::TYPE_DECREMENT,
            "description" => $description,
            "quantity" => $quantity,
            'order_id' => $order_id,
            'store_id' => $store->id,
            // we don't store mini_order_id in Benedito project. but it might to exists in other projects
//            'mini_order_id' => $mini_order_id,
        ]);

        $store->decrement('balance', $transaction->quantity);
        //example $store->increment('balance', $transaction->quantity);
        //example $store->decrement('balance', $transaction->quantity);
        $this->updateStoreBalanceInCache($store->id, $store->balance);
        return $store;
    }


    private function updateStoreBalanceInCache($store_id, $newBalance): void
    {
        $stores = $this->cacheData;

        foreach ($stores as $store) {
            if ($store->id == $store_id) {
                $store->balance = $newBalance;
                break;
            }
        }
        Cache::put(self::getCacheName(), $stores);
    }

    public static function productStoreBalanceCalculator($product_id): int {
        $variety_ids = (new ProductsCollectionService())->getActiveVarietiesIds($product_id);
        $productStoreBalance = 0;
        foreach ($variety_ids as $variety_id)
            $productStoreBalance += (new StoreBalanceService($variety_id))->getBalance();

        return $productStoreBalance;
    }

}
