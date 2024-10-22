<?php

namespace Modules\Report\Entities;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\OrderItem;
use Modules\Shipping\Entities\Shipping;
use Modules\Order\Entities\Order;
//use Shetabit\Shopit\Modules\Report\Entities\OrderReport as BaseReport;

class OrderReport extends Order
{
    public static function getMaxTotalForFilter()
    {
        $max = DB::table('orders')->max('total_amount');
        $m = $max/1000000;
        $m = ceil($m);
        $m *= 1000000;
        return $m;
    }

    public static function getMaxItemsCountForFilter()
    {
        $max = DB::table('orders')->max('items_quantity');
        $m = $max/10;
        $m = ceil($m);
        $m *= 10;
        return $m;
    }






    // came from vendor ================================================================================================
    protected $table = 'order_reports_view';

    protected $appends = ['product_info', 'attribute_info', 'color_info'];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipping(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    public function coupon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id')
            ->with(['variety' => function($query){
                $query->withCommonRelations();
            }]);
    }

    public function activeItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->items()->where('status', '=', 1);
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function getProductInfoAttribute()
    {
        return $this->productAndColorHelper($this->product_ids);
    }

    public function getColorInfoAttribute()
    {
        return $this->productAndColorHelper($this->color_ids);
    }

    public function getAttributeInfoAttribute()
    {
        $orderGroups = explode('!##!', $this->attribute_ids);
        $keyValue = [];
        foreach ($orderGroups as $orderGroup) {
            $attributeIds = explode('!#!', $orderGroup);
            foreach ($attributeIds as $attributeId) {
                if ($attributeId == '') continue;
                $parts = explode('---', $attributeId);
                // این ایف الکیه باید بررسی بشه چرا خالی میشه
                if (!isset($parts[1])) {
                    continue ;
                }
                $quantity = (int)$parts[1];
                $parts2 = explode('||', $parts[0]);
                // این ایف الکیه باید بررسی بشه چرا خالی میشه قسمت سوم البته
                if (!$parts2 || !isset($parts2[0]) || !json_decode($parts2[0])) {
                    continue;
                }
                foreach (json_decode($parts2[0]) as $key => $attr) {
                    $attributeName = json_decode($parts2[0])[$key];
                    $attributeValue = json_decode($parts2[1])[$key];
                    if (!isset($keyValue[$attributeName])) {
                        $keyValue[$attributeName] = [];
                    }
                    if (!isset($keyValue[$attributeName][$attributeValue])) {
                        $keyValue[$attributeName][$attributeValue] = $quantity;
                    } else {
                        $keyValue[$attributeName][$attributeValue] += $quantity;
                    }
                }

            }
        }


        return $keyValue;
    }

    protected function productAndColorHelper($data)
    {
        $ids = str_replace('"', '', $data);
        $ids = explode(',', $ids);
        $keyValue = [];
        foreach ($ids as $id) {
            if ($id == '') continue;
            $realId = explode('-', $id);
            $quantity = (int)$realId[1];
            $realId = (int)$realId[0];
            if (!isset($keyValue[$realId])) {
                $keyValue[$realId] = $quantity;
            } else {
                $keyValue[$realId] += $quantity;
            }
        }

        return $keyValue;
    }

    public function getStatusAttribute($status)
    {
        return $status;
    }

    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Order\Entities\Order::class, 'reserved_id', 'id')->withCommonRelations();
    }

    public function getMorphClass()
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array(\Modules\Order\Entities\Order::class, $morphMap)) {
            return array_search(\Modules\Order\Entities\Order::class, $morphMap, true);
        }

        return \Modules\Order\Entities\Order::class;
    }

    public function getStatusesInfoAttribute()
    {
        $statuses = [];
        foreach (Order::getAvailableStatuses() as $status) {
            $statuses[$status] = substr_count($this->statuses, $status);
        }
        return $statuses;
    }

    public function getOrderInfoAttribute()
    {
        return collect(explode(',', $this->order_ids))
            ->map(fn($id) => ['id' => (int)$id]);
    }

    public function getCountAttribute()
    {
        return substr_count($this->statuses, ',') + 1;
    }

    public function scopeHasCategoryIds($query, $categoryIds)
    {
        $categoryIds = is_array($categoryIds) ? $categoryIds : [$categoryIds];

        $query->whereHas('activeItems', function ($query) use ($categoryIds) {
            $query->whereHas('product', function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('id', $categoryIds);
                });
            });
        });
    }

    public function scopeHasBrand($query, $brandIds)
    {
        $brandIds = is_array($brandIds) ? $brandIds : [$brandIds];

        $query->whereHas('activeItems', function ($q) use ($brandIds) {
            $q->whereHas('product', function ($q2) use ($brandIds) {
                $q2->whereIn('brand_id', $brandIds);
            });
        });
    }




}
