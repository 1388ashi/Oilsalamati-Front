<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Variety;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Setting\Entities\Setting;
//use Shetabit\Shopit\Modules\Cart\Entities\Cart as BaseCart;

class Cart extends Model
{
    protected $fillable = [
        'quantity',
        'variety_id',
        'customer_id',
        'discount_price',
        'price'
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public static function calculate_sum_carts($carts) {
        $sum_carts = 0;
        foreach ($carts as $cart) {
            $sum_carts += $cart->price * $cart->quantity;
        }
        return $sum_carts;
    }
    public static function calculate_sum_discounts_carts($carts) {
        $sum_discount_carts = 0;
        foreach ($carts as $cart) {
            $sum_discount_carts += ($cart->discount_price * $cart->quantity);
        }
        return $sum_discount_carts;
    }


    public static function getCartsWeight($carts = null, $customer = null)
    {
        //get carts
        $carts = $carts ?? $customer->carts;
//        $carts = ($this->byAdmin) ? $this->checkSumPriceWhenAdmin() :  $this->customer->carts;
        $weight = 0;

//        //Get Card Weight
        foreach ($carts as $cart) {
            $iweight = Setting::getFromName('defualt_product_weight') ? Setting::getFromName('defualt_product_weight') : 120;
            if($cart->variety?->weight){
                $iweight = $cart->variety->weight;
            }elseif($cart->variety->product?->weight){
                $iweight = $cart->variety->product->weight;
            }

            $weight = $weight + ($cart->quantity * $iweight);
        }

        return $weight;
    }

    public static function has_free_shipping_product($carts) : bool {
        foreach ($carts as $cart) {
            if ($cart->variety->product->free_shipping) {
                return true;
            }
        }
        return false;
    }

    public static function has_free_shipping_product_by_variety_ids($variety_ids) : bool {
        foreach ($variety_ids as $variety_id) {
            if (Variety::find($variety_id)->product->free_shipping) {
                return true;
            }
        }
        return false;
    }

    public static function fakeCartMakerWithOrderItems($orderItems)
    {
        $fakeCarts = [];
        foreach ($orderItems as $orderItem) {
            $newFakeCart = new Cart([
                'variety_id' => $orderItem->variety_id,
                'quantity' => $orderItem->quantity,
                'discount_price' => $orderItem->discount_amount,
                'price' => $orderItem->amount,
            ]);
            $newFakeCart->load(['variety' => function ($query) {$query->with('product');}]); /* todo: because of DontAppend method in final_price method in Variety, we are have to load product to have final_price attribute here. */
            $fakeCarts[] = $newFakeCart;
        }

        return collect($fakeCarts);
    }

    public static function fakeCartMaker($variety_id,$quantity,$discount_price,$price):Cart
    {
        return new Cart([
            'variety_id' => $variety_id,
            'quantity' => $quantity,
            'discount_price' => $discount_price,
            'price' => $price,
        ]);
    }



    // came from vendor ================================================================================================
    protected $appends = [
    ];

    protected static $commonRelations = [/*'variety.product.unit', 'variety.product.varieties.attributes', 'variety.color',
        'customer', 'variety.attributes.pivot.attributeValue'*/];

    /**
     * Relations Function
     */
    public function variety(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function scopeOwner($query)
    {
        $query->where('customer_id', auth()->user()->id);
    }

    public function setDiscountPrice($variety)
    {
        $this->attributes['discount_price'] = $variety->final_price['discount_price'];
    }

    public function setPrice($variety)
    {
        $this->attributes['price'] = $variety->final_price['amount'];
    }

    public static function addToCart($quantity, Variety $variety, Customer $customer)
    {
        $cart = new \Modules\Cart\Entities\Cart([
            'quantity' => $quantity,
            'variety_id' => $variety->id
        ]);
        #set variety in CartStoreRequest
        $cart->setDiscountPrice($variety);
        $cart->setPrice($variety);
        $cart->customer()->associate($customer);
        $cart->variety()->associate($variety);
        $cart->save();
        $cart->unsetRelation('customer');
//        $cart->loadCommonRelations();

        return $cart;
    }

    public function getReadyForFront():void
    {
        $variety = (new ProductsCollectionService())->getVarietyObjectFromVarietyId($this->variety_id);
        $variety->setAppends(['title','main_image_showcase']);
        $this->variety = $variety;
        $this->cart_price_amount = $this->quantity * $this->price;
        $this->cart_discount_price_amount = $this->quantity * $this->discount_price;
        $this->unsetRelation('variety');
    }



}
