<?php

namespace Modules\Report\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;
//use Shetabit\Shopit\Modules\Report\Entities\ProductReport as BaseProductReport;
//use Modules\Core\Entities\BaseModel;

class ProductReport extends Model implements \Modules\Report\Contracts\ProductReport
{
    public function product()
    {
        return $this->belongsTo(Product::class,'id','id');
    }


    // came from vendor ================================================================================================
    protected $table = 'product_reports_view';

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category',
            'product_id', 'category_id');
    }
    public function scopeFilters($query)
    {
        $hasActiveDiscount = \request()->boolean('has_active_discount');
        $title = request('title');
        $startDate = request('start_date');
        $endDate = request('end_date');

        return $query
            ->when($startDate && $endDate, function(Builder $query) use ($startDate, $endDate) {
                $query->whereHas('product', function (Builder $query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
            })
            ->when($hasActiveDiscount,function ($q) use($hasActiveDiscount){
                return $q->whereHas('product', function($q){
                    $q->where('discount', '>',0);
                });
            })
            ->when($title, fn($query) => $query->where('title', 'like', "%$title%"));
    }
}
