<?php

namespace Modules\Store\Entities;

//use Shetabit\Shopit\Modules\Store\Entities\StoreTransaction as BaseStoreTransaction;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Services\ProductsCollectionService;

class StoreTransaction extends Model
{
    protected $fillable = [
        'type',
        'description',
        'quantity',
        'mini_order_id',
        'order_id',
        'store_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function scopeFilters($query)
    {
        return $query
            ->when(request('variety_id'), function (Builder $query) {
                $query->whereHas('store', function ($storeQuery) {
                    $storeQuery->where('variety_id', \request('variety_id'));
                });
            })
            ->when(request('product_id') && !request('variety_id'), function (Builder $query) {
                $varietyIds = (new ProductsCollectionService())->getAllVarietiesIds(request('product_id'));
                $query->whereHas('store', function ($storeQuery) use ($varietyIds){
                    $storeQuery->whereIn('variety_id', $varietyIds);
                });
            })
            ->when(request('id'), function (Builder $query) {
                $query->where('id', request('id'));
            })
            ->when(request('type'), function (Builder $query) {
                $query->where('type', request('type'));
            })
            ->when(request('start_date'), function (Builder $query) {
                $query->whereDate('created_at', '>=', request('start_date'));
            })
            ->when(request('end_date'), function (Builder $query) {
                $query->whereDate('created_at', '<=', request('end_date'));
            });
    }
}
