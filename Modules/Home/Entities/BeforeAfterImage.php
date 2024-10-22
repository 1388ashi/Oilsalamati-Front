<?php

namespace Modules\Home\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Product\Entities\Product;
use Spatie\MediaLibrary\HasMedia;

class BeforeAfterImage extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'short_description',
        'full_description',
        'customer_name',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
