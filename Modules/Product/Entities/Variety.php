<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Entities\Admin;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeValue;
use Modules\Cart\Entities\Cart;
use Modules\Color\Entities\Color;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\DontAppend;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Cache\CacheForgetService;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Services\ProductDetailsService;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Store\Entities\Store;
use Modules\Store\Services\StoreBalanceService;
use Spatie\MediaLibrary\HasMedia;

//use Shetabit\Shopit\Modules\Product\Entities\Variety as BaseVariety;

class Variety extends Model implements HasMedia
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'SKU',
        'barcode',
        'purchase_price',
        'discount_until',
        'discount_type',
        'discount',
        'order',
        'max_number_purchases',
        'weight',
        'max_limit',
        'is_head'
    ];

    protected $hidden = ['media','price','purchase_price','discount_type','discount','discount_until','colleague_price','created_at','updated_at'];


    public function attributes_showcase_loader()
    {
        $showcase = [];
        foreach ($this->attributes()->get() as $attribute) {
            $showcase[$attribute->label] = $attribute->pivot->attributeValue->value;
        }
        $this->attributes_showcase = $showcase;
//        $this->makeHidden('attributes');
    }



    // came from vendor ================================================================================================

    use InteractsWithMedia, SoftDeletes;

//    protected $appends = ['unique_attributes_key', 'images', 'quantity', 'final_price', 'final_gifts'];
//    protected $appends = ['final_price', 'store_balance', 'images_showcase'];


    /** @see Product::toArray() */
    public $dontToArrayProduct = false;

    protected $loadDiscount = false;

    protected static $commonRelations = [/*'color', 'attributes', 'product'*/];

    CONST ACCEPTED_IMAGE_MIMES = 'gif|png|jpg|jpeg|svg|webp';

    /**
     *  Discount Type @const
     */
    const DISCOUNT_TYPE_PERCENTAGE = "percentage";
    const DISCOUNT_TYPE_FLAT       = "flat";

//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $with = app(CoreSettings::class)->get('product.variety.front.with');
//        $gift = app(CoreSettings::class)->get('product.gift.active');
//        if (!empty($with)) {
//            $this->with = array_merge($this->with,$with);
//        }
//        if ($gift) {
//            if (auth()->user() instanceof Admin) {
//                $this->with = array_merge($this->with,['activeGifts', 'gifts']);
//            }else{
//                $this->with = array_merge($this->with,['activeGifts']);
//            }
//        }
//    }

    protected static function booted()
    {
        parent::booted();

        static::updating(function ($variety){
            if($variety->isDirty('max_number_purchases')){
                Cache::forget('variety-quantity-' . $variety->id);
            }
//            ProductDetailsService::forgetCache($this->product_id);
            CacheForgetService::run($variety);
        });

        static::deleting(function (\Modules\Product\Entities\Variety $variety){
            if (!$variety->orderItems()->exists() && !$variety->isForceDeleting()){
                $variety->forceDelete();
            }

            Cart::query()->where('variety_id', $variety->id)->get()->each(function ($cart) {
                $cart->delete();
            });
//            ProductDetailsService::forgetCache($this->product_id);
            CacheForgetService::run($variety);
        });

        static::creating(function ($variety){
            if($variety->isDirty('max_number_purchases')){
                Cache::forget('variety-quantity-' . $variety->id);
            }
        });
    }

//    protected function getArrayableRelations()
//    {
////        return [];
//        $result = $this->getArrayableItems($this->relations);
//        if ($this->dontToArrayProduct) {
//            unset($result['product']);
//        }
//
//        return $result;
//    }


    #Relations function
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_variety', 'variety_id')
            ->using(VarietyAttributeValuePivot::class)
            ->withPivot('attribute_value_id' , 'value');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'variety_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variety_id');
    }

    public function gifts(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Product\Entities\Gift::class, 'gift_product_variety', 'variety_id')
            ->withPivot('should_merge');
    }

    public function activeGifts()
    {
        return $this->gifts()->active();
    }
    #End Relations

    /**
     * Register Spatie Media library
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function addImages($images)
    {
        Media::addMedia($images, $this, 'images');
    }

    public function updateImages($images): void
    {
        $updatedImages = Media::updateMedia($images , $this, 'images');
        $mediaToDelete = $this->media()->whereNotIn('id', $updatedImages)->get();
        foreach ($mediaToDelete as $media) {
            $media->delete();
        }
    }

    // کلید یکتا براساس رنگ و ویژگی های چسبیده شده
    // فعلا بلا استفاده
    public function getUniqueAttributesKeyAttribute()
    {
        if (!$this->relationLoaded('attributes')) {
            return new DontAppend('Variety getUniqueKeyAttribute');
        }
        /** @var Collection $attributes */
        $attributes = $this->getRelation('attributes');
        $attributesString = $attributes->map(function ($attribute) {
            return $attribute->id . $attribute->pivot->value . $attribute->pivot->attribute_value_id;
        })->join('');

        return 'c' . $this->color_id . 'a' . $attributesString;
    }

    public function getImagesAttribute()
    {
        if (!$this->relationLoaded('media')) {
            return new DontAppend('Variety getImagesAttribute');
        }
        $allImages = $this->getMedia('images');
        if (!$allImages){
            return null;
        }

        return MediaResource::collection($allImages);
    }

    public function getImagesShowcaseAttribute()
    {
        return (new ProductDetailsService($this->product_id))->getVarietyMedias($this->id);
    }

    /**
     *  Set Discount Type Product
     */
    public function getAvailableDiscountTypes(): array
    {
        return [static::DISCOUNT_TYPE_FLAT, static::DISCOUNT_TYPE_PERCENTAGE];
    }

    public function setDiscountTypeAttribute($discountType)
    {
        if (($discountType != null) && !in_array($discountType , $this->getAvailableDiscountTypes())){
            throw  Helpers::makeValidationException('نوع تخفیف وارد شده نامعتبر است');
        }
        $this->attributes['discount_type'] = $discountType;
    }

    public function setBarcodeAttribute($value)
    {
        $this->attributes['barcode'] = Helpers::convertFaNumbersToEn($value);
    }

    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = Helpers::convertFaNumbersToEn($value);
    }

    public static function storeVarieties($varietyRequest , Product $product)
    {
        foreach ($varietyRequest as $variety) {
            static::storeVariety($variety , $product);
        }
    }

    /**
     * @throws \Exception
     */
    public static function storeVariety($varietyRequest , Product $product)
    {
        $variety = new static;
        $variety->fill($varietyRequest);
        $variety->product()->associate($product);
        if($varietyRequest['color_id']){
            $variety->color()->associate($varietyRequest['color_id']);
        }
        $variety->save();
        $store = (new self)->setStoreParams(
            $variety->id,
            Store::TYPE_INCREMENT,
            'موجودی اولیه محصول',
            $varietyRequest['quantity']
        );
        Store::insertModel($store, true);
        $variety->addImages($varietyRequest['images']);

        if ($attributes = $varietyRequest['attributes']){
            $variety->assignAttributes($attributes);
        }
        $variety->assignGifts($varietyRequest);

        return $variety;
    }

    public static function updateVarieties($varietyRequest, Product $product)
    {
        $varietyIds = [];
        foreach ($varietyRequest as $variety) {
            if (isset($variety['id']) && $variety['id']){
                $varietyIds[] = $variety['id'];
            }
            $variety = static::updateVariety($variety, $product);
            if ($variety != null){
                $varietyIds[] = $variety->id;
            }
        }
        /**
         * زمانی که محصول آپدیت میشود اگر ایدی های تنوع ارسال نشه به این معنی هست که اون تنوع حذف شده.
         */
        $product->varieties()->whereNotIn('varieties.id', $varietyIds)
            ->get()->map(function ($v) {
                $v->delete();
            });
    }

    public static function updateVariety($varietyRequest, Product $product)
    {
        // اگر شناسه null باشد به این معنی است که تنوع جدید داره ثبت میشود.
        if (!isset($varietyRequest['id']) || !$varietyRequest['id']){
            (new self)->deleteFakeVariety($product);
            return static::storeVariety($varietyRequest , $product);
        }

        $variety = static::query()->findOrFail($varietyRequest['id']);

        $variety->fill($varietyRequest);
        if($varietyRequest['color_id']){
            $variety->color()->associate($varietyRequest['color_id']);
        }
        $variety->save();

        $store = (new static)->updateStore($variety, $varietyRequest['quantity']);
        Store::insertModel($store);

        $variety->updateImages($varietyRequest['images']);

        if ($attributes = $varietyRequest['attributes']){
            $variety->assignAttributes($attributes);
        }

        $variety->assignGifts($varietyRequest);

    }

    public function assignGifts($varietyRequest)
    {
        if (isset($varietyRequest['gifts'])){
            foreach ($varietyRequest['gifts'] as $gift) {
                $this->gifts()->sync($gift['id']);
            }
        }
    }

    public function assignAttributes(array $attributes)
    {
        $this->attributes()->detach();
        foreach ($attributes as $attribute){
            if (is_integer($attribute['value'])){
                $attributeValue = AttributeValue::query()->where('id' , $attribute['value'])->first();
                $this->attributes()->attach($attribute['id'], ['attribute_value_id' => $attributeValue->id , 'value' => $attributeValue->value]);
            }
            if (is_string($attribute['value'])){
                $this->attributes()->attach($attribute['id'], ['value' => $attribute['value']]);
            }
        }
    }

    /**
     * زمانی که محصول تنوع نداشت یه تنوع فیک برای ان ایجاد میکنیم.
     * @throws \Exception
     */
    public static function storeFakeVariety(Product $product, $quantity)
    {
        $variety = $product->varieties()->first();
        if ($variety) {
            if (!$variety->isFake()) {
                $product->varieties()->delete();
                $variety = new static;
                $variety->product()->associate($product);
            }
        } else {
            $variety = new static;
            $variety->product()->associate($product);
        }
        $updating = (bool)$variety->id;
        $variety->fill([
            'price' => $product->unit_price,
            'SKU' => $product->SKU,
            'barcode' => $product->barcode,
            'purchase_price' => $product->purchase_price,
            'discount_type' => $product->discount_type,
            'discount' => $product->discount,
        ]);
        $variety->save();
        if (!$updating) {
            $store = (new self)->setStoreParams(
                $variety->id,
                Store::TYPE_INCREMENT,
                'موجودی اولیه محصول',
                $quantity
            );
        } else {
            $store = (new static)->updateStore($variety, $quantity);
        }

        Store::insertModel($store, true);
    }

    public function isAvailable()
    {
        return $this->quantity != 0 && $this->product->isAvailable();
    }

    public function getFinalPriceAttribute()
    {
        $service = new ProductDetailsService($this->product_id);
        return $service->getVarietyFinalPrice($this->id);
    }

    public function getFinalGiftsAttribute()
    {
        if (!$this->relationLoaded('activeGifts')) {
            return new DontAppend('getFinalGiftsAttribute');
        }

        $finalGifts = collect();

        foreach ($this->activeGifts as $gift) {
            $finalGifts->push($gift);
        }

        foreach ($this->product->activeGifts as $gift) {
            if ($gift->pivot->should_merge){
                $finalGifts->push($gift);
            }
        }

        return $finalGifts->unique();
    }

    /**
     * @param $model
     * @param int $price
     * @param string $name
     * @return array
     */
    public static function calculateDiscount($model, int $price, string $name): array
    {
        $appliedDiscountType = $name;
        if ($model->discount_type == static::DISCOUNT_TYPE_FLAT){
            $appliedDiscountPrice = $model->discount;
            $discountType =  $model->discount_type;
        }else{
            $appliedDiscountPrice = (int)round(($model->discount * $price) / 100);
            $discountType =  static::DISCOUNT_TYPE_PERCENTAGE;
        }
        $finalPricePrice = $price - $appliedDiscountPrice;

        return [
            'discount_model'  => $appliedDiscountType,
            'discount_type'  => $discountType,
            'discount'  => $model->discount,
            'discount_price' => $appliedDiscountPrice,
            'amount'      => $finalPricePrice
        ];
    }

    public function getQuantityAttribute()
    {
        return Cache::rememberForever('variety-quantity-' . $this->id, function () {
            $balance = $this->store->balance ?? 0;
            return min($balance,$this->max_number_purchases);
        });
    }

    public function getStoreBalanceAttribute() :int
    {
        return (new StoreBalanceService($this->id))->getBalance();
    }

    public function setStoreParams(int $variety, string $type, string $description, int $quantity)
    {
        return (object)[
            'type' => $type,
            'description' => $description,
            'quantity' => $quantity,
            'variety_id' => $variety
        ];
    }

    public function deleteFakeVariety($product)
    {
        $fakeVariety = $product->varieties()->get();
        $fakeVariety->each(function ($item) use ($fakeVariety){
            /** @var $item static */
            if($item->isFake()){
                $item->delete();
            }
        });
    }

    public function isFake()
    {
        return !$this->attributes()->exists() && $this->color_id == null;
    }

    /**
     * @throws \Exception
     * این فقط متد کمکیه و کاری نمیکنه
     */
    public function updateStore($variety, $quantity)
    {
        $store = Store::query()->where('variety_id', $variety->id)->first();
        if (!$store) {
            $store = $variety->store()->create([
                'balance' => 0
            ]);
        }
        $balance = $store->balance;

        if($balance > $quantity) {
            $diff =  $balance - $quantity;//decrement
            $store = (new self)->setStoreParams(
                $variety->id,
                Store::TYPE_DECREMENT,
                "بروزرسانی موجودی محصول کاهش {$diff} عددی",
                ($diff < 0) ? 0 : $diff
            );
        }
        else if ($balance < $quantity){
            $diff = $quantity - $balance; // increment
            $store = (new self)->setStoreParams(
                $variety->id,
                Store::TYPE_INCREMENT,
                "بروزرسانی موجودی محصول افزایش {$diff} عددی",
                $diff
            );
        } else {
            return null;
        }

        return $store;
    }

    // For notification
    public function getMainImageAttribute()
    {
        $media = $this->getFirstMedia('images');
        if ($media) {
            return $media->getFullUrl();
        }

        return null;
    }
    public function getMainImageShowcaseAttribute() {
        return (new ProductDetailsService($this->product_id))->getVarietyMainImage($this->id);
    }


    public function getTitleAttribute():string {
        return (new ProductDetailsService($this->product_id))->getVarietyTitle($this->id);
//        $product = ((new ProductsCollectionService())->getProductsCollection())->where('id', $this->product_id)->first();
//        $title = $product->title ;
//        $title .= $this->color->name ?? '';
//
//        foreach ($this->relations['attributes'] ?? [] as $attribute) {
//            $title .= ' | '.$attribute->label.': '.$attribute->pivot->value;
//        }
//
//        return $title;
    }

    // SCOPES ==========================================================================================================
    public function scopeActive($query) {
        return $query->whereNull('deleted_at');
    }
    public function scopeNotActive($query)
    {
        return $query->whereNotNull('deleted_at');
    }
    // END SCOPES ======================================================================================================

    public function getColorShowcaseAttribute():array|null {
        return (new ProductDetailsService($this->product_id))->getVarietyColorShowcase($this->id);
    }
    public function getAttributesShowcaseAttribute():array|null {
        return (new ProductDetailsService($this->product_id))->getVarietyAttributesShowcase($this->id);
    }

    public function getTotalSalesAttribute()
    {
        $ordersItems = $this->orderItems->where('status', 1);

        $totalSalesAmount = $ordersItems->map(function($item) {
            return $item->amount * $item->quantity;
        })->sum();

        $totalSalesCount = $ordersItems->sum('quantity');

        return [
            'sales_count' => $totalSalesCount,
            'sales_amount' => $totalSalesAmount,
        ];
    }

}
