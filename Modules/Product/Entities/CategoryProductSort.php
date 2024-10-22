<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Category\Entities\Category;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Services\Cache\CacheForgetService;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\EloquentSortable\Sortable;
use \Kyslik\ColumnSortable\Sortable as SpatieSortable;

class CategoryProductSort extends Model implements Sortable
{
    use SortableTrait,SpatieSortable;

    protected $fillable = [
        'category_id',
        'product_id',
        'order'
    ];

    public array $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];


    public static function booted()
    {
        static::deleting(function ($categoryProductSort) {
            CacheForgetService::run($categoryProductSort);
        });
        static::updating(function ($categoryProductSort) {
            CacheForgetService::run($categoryProductSort);
        });
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public static function getMaxOrder(): int
    {
        return (int) self::query()->max('order') + 1;
    }

    public static function sortOrders(Model $model, int $order, $category_id): void
    {
        $id = $model->id;
        $oldOrder = $model->order;
        $orders = [];

        // Retrieve all items within the same category
        $orderedServices = $model->query()
            ->ordered()
            ->where('category_id', $category_id)
            ->get(['id', 'order']);

        // Update orders based on the new order
        foreach ($orderedServices as $service) {
            if ($service->order == $oldOrder) {
                // Update order of the item being moved
                $service->order = $order;
            } elseif ($order < $oldOrder && $service->order >= $order && $service->order < $oldOrder) {
                // Shift order for items below the new position
                $service->order++;
            } elseif ($order > $oldOrder && $service->order <= $order && $service->order > $oldOrder) {
                // Shift order for items above the new position
                $service->order--;
            }
            $service->save();
        }
    }




}
