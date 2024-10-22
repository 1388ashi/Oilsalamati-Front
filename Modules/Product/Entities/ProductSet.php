<?php

namespace Modules\Product\Entities;

//use Shetabit\Shopit\Modules\Product\Entities\ProductSet as BaseProductSet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Traits\HasAuthors;
//use Modules\Product\Entities\Product;

class ProductSet extends Model
{
    use HasAuthors/*, HasCommonRelations*/;

    protected $fillable = [
        'name'
    ];

//    protected static $commonRelations = [
//        'products'
//    ];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_set_product');
    }

    public static function storeAndUpdateModel(Request $request, \Shetabit\Shopit\Modules\Product\Entities\ProductSet $productSet = null)
    {
        $set = $productSet ?? new static();
        $set->fill($request->all());
        $set->save();
        $set->products()->sync($request->input('product_ids'));
        $set->load('products');

        return $set;
    }
}
