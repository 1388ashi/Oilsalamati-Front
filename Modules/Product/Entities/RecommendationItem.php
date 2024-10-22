<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\SortableTrait;

class RecommendationItem extends Model implements Sortable
{
    use HasFactory, SortableTrait;
    protected $table = 'recommendation_items';
    protected $fillable = ['product_id', 'priority', 'recommendation_id'];
    protected $defaults = [
        'priority' => 1
    ];
    public $sortable = [
        'order_column_name' => 'priority',
        'sort_when_creating' => true,
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(Recommendation::class, );
    }

}
