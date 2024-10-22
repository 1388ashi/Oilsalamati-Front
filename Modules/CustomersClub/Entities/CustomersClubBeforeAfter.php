<?php

namespace Modules\CustomersClub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Spatie\MediaLibrary\HasMedia;

class CustomersClubBeforeAfter extends Model implements HasMedia
{
    use HasFactory , InteractsWithMedia;

    protected $fillable = ['customer_id','product_id', 'description','type'];

    protected static function newFactory()
    {
        return \Modules\CustomersClub\Database\factories\CustomersClubBeforeAfterFactory::new();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
}
