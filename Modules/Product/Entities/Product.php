<?php

namespace Modules\Product\Entities;

use Carbon\Carbon;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Kyslik\ColumnSortable\Sortable as SpatieSortable;
use Modules\Admin\Entities\Admin;
use Modules\Blog\Entities\Post;
use Modules\Brand\Entities\Brand;
use Modules\Category\Entities\Category;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\DontAppend;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Helpers\Str;
use Modules\Core\Services\Cache\CacheForgetService;
use Modules\Core\Traits\HasDefaultFields;
use Modules\Core\Traits\HasMorphAuthors;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Customer\Entities\Customer;
use Modules\Flash\Entities\Flash;
use Modules\Home\Services\HomeService;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Services\ProductDetailsService;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Product\Services\ProductService;
use Modules\ProductComment\Entities\ProductComment;
use Modules\SizeChart\Entities\SizeChart;
use Modules\Specification\Entities\Specification;
use Modules\Specification\Entities\SpecificationValue;
use Modules\Store\Services\StoreBalanceService;
use Modules\Unit\Entities\Unit;
//use Spatie\Activitylog\LogOptions;
//use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\HasTags;

//use Shetabit\Shopit\Modules\Product\Entities\Product as BaseProduct;

class Product extends Model implements HasMedia, Viewable
{
    use InteractsWithMedia, InteractsWithViews, HasMorphAuthors,
        HasTags, HasDefaultFields/*, LogsActivity*/ ,SortableTrait, SpatieSortable;

    const COLLECTION_NAME_IMAGES = 'images';
    const COLLECTION_NAME_IMAGES_MOBILE = 'images_mobile';
    const SELECTED_COLUMNS_FOR_FRONT = ['products.id','products.title','products.status','products.slug','products.free_shipping'];
    const APPENDS_LIST_FOR_FRONT = ['final_price','images_showcase'];

    protected $defaults = [ 'chargeable' => 0 ];
    public array $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'title',
        'short_description',
        'description',
        'unit_price',
        'colleague_price',
        'purchase_price',
        'discount_type',
        'discount_until',
        'discount',
        'SKU',
        'barcode',
        'brand_id',
        'unit_id',
        'meta_description',
        'meta_title',
        'low_stock_quantity_warning',
        'show_quantity',
        'chargeable',
        'status',
        'approved_at',
        'published_at',
        'slug',
        'weight',
        'max_limit',
        'new_product_in_home',
        'order',
        'is_package',
        'is_benibox',
        'is_amazing',
        'free_shipping'
    ];
    protected $hidden = ['media', 'updated_at', 'unit_price', 'purchase_price'];
    public $appends = [/*'final_price', 'images_showcase'*/];
    public array $dontHide = [];


    /** @const Status */
    const STATUS_DRAFT = "draft";  # چرک نویس -> غیرقابل فروش -> غیرقابل نمایش -> در v2
    const STATUS_SOON = "soon";  # به زودی -> غیرقابل فروش -> قابل نمایش
    const STATUS_AVAILABLE = "available";  # موجود -> قابل فروش -> قابل نمایش
    const STATUS_OUT_OF_STOCK = "out_of_stock"; # ناموجود -> غیرقابل فروش ->  قابل نمایش
    const STATUS_AVAILABLE_OFFLINE = "available_offline"; #  موجود -> قابل فروش ->  قابل نمایش نمیباشد -> فروش فقط توسط ادمین

    /** @const Discount Type */
    const DISCOUNT_TYPE_PERCENTAGE = "percentage";
    const DISCOUNT_TYPE_FLAT = "flat";

    const ACCEPTED_IMAGE_MIMES = 'gif|png|jpg|jpeg|svg|webp';
    const DATE_FORMAT = 'Y-m-d H:i:s';

    public static function getStatusLabelAttribute($status)
    {
        return match ($status) {
            self::STATUS_DRAFT => 'پیش نویس',
            self::STATUS_SOON => 'به زودی',
            self::STATUS_AVAILABLE => 'موجود',
            self::STATUS_OUT_OF_STOCK => 'ناموجود',
            self::STATUS_AVAILABLE_OFFLINE => 'موجود',
        };
    }


    protected static function booted()
    {
        static::creating(function ($product) {
            $user = auth()->user();
            ProductService::deleteCache();
            if ($user instanceof Admin && ($user->can('approved_product'))) {
                $product->approved_at = Carbon::now()->toDateTimeString();
            }
            if (is_null($product->published_at)) {
                $product->published_at = now();
            }
        });
        static::updating(function ($product) {
            CacheForgetService::run($product);
//            ProductService::deleteCache();
//            HomeService::deleteCache();
//            ProductDetailsService::forgetCache($product->id);
        });
        static::deleting(function (\Modules\Product\Entities\Product $product) {
            if ($product->orderItems()->exists()) {
                throw Helpers::makeValidationException('به علت وجود سفارش برای این محصول امکان حذف آن وجود ندارد');
            }
            if ($rec = $product->recommendations()->first()) {
                $name = __('core::groups.' . $rec->group);
                throw Helpers::makeValidationException("این محصول در لیست محصولات پیشهادی ($name) انتخاب شده است ");
            }
//            ProductService::deleteCache();
//            HomeService::deleteCache();
//            ProductDetailsService::forgetCache($this->id);
            CacheForgetService::run($product);
        });
    }

    // =================================================================================================================
    // SCOPES ==========================================================================================================
    public function scopeSortByCategory($query)
    {
        if (request()->has('category_id')) {
            $category_id = request()->category_id;

            $sorts = CategoryProductSort::query()
                ->where('category_id', $category_id)
                ->orderBy('order')
                ->pluck('product_id')
                ->toArray();

            if (!empty($sorts)) {
                $query->orderByRaw(DB::raw("FIELD(id, " . implode(',', $sorts) . ")"));
            }
        }

        return $query;
    }
    public function scopeActive($query, $operatorStatus = '!=', $status = self::STATUS_DRAFT)
    {
        $query->where('status', $operatorStatus, $status)
            ->whereNotNull('approved_at');

        $customer = Auth::guard('customer-api')->user();
        if (!($customer instanceof Customer) || !($customer->canSeeUnpublishedProducts())) {
            $query->where('published_at', "<=", Carbon::now());
        }
    }
    // کالا های قابل خرید
    public function scopeAvailable($query, $force = false)
    {
        $query->where('status', self::STATUS_AVAILABLE)
            ->whereNotNull('approved_at');

        $customer = Auth::guard('customer-api')->user();
        if ($force || !($customer instanceof Customer) || !($customer->canSeeUnpublishedProducts())) {
            $query->where('published_at', "<=", Carbon::now());
        }
    }
    public function scopeFilters($query)
    {
        return $query
            ->when((\request()->has('id') && request()->id != null), function (Builder $q) {
                $q->where('id', \request('id'));
            })
            ->when((\request()->has('title') && request()->title != null), function (Builder $q) {
                $q->whereRaw("title LIKE '%" . \request('title') . "%'");
            })
            ->when((\request()->has('status') && request()->status != null), function (Builder $q) {
                $q->where('status', \request('status'));
            })
            ->when(\request()->has('approved'), function (Builder $q) {
                if (\request('approved'))
                    $q->whereNotNull('approved_at');
                else
                    $q->whereNull('approved_at');
            })
            ->when(\request('start_date'), function (Builder $q) {
                $q->where('created_at', '>=', \request('start_date'));
            })
            ->when(\request('end_date'), function (Builder $q) {
                $q->where('created_at', '<=', \request('end_date'));
            });
    }
    public function scopeApproved_at($query) {
        $approved_at = request()->input('approved_at', 2); // Default to 2 if not set

        return match ($approved_at) {
            0 => $query->whereNull('approved_at'),
            1 => $query->whereNotNull('approved_at'),
            default => $query,
        };
    }

    // SCOPES ==========================================================================================================
    // =================================================================================================================

    // =================================================================================================================
    // ATTRIBUTES ======================================================================================================
    public function getFinalPriceAttribute() {
        $service = new ProductDetailsService($this->id);
        return $service->getProductFinalPrice();
    }
    public function getImagesAttribute()
    {
        if (!$this->relationLoaded('media')) {
            return new DontAppend('Product getImagesAttribute');
        }
        $media = $this->getMedia('images');

        return MediaResource::collection($media);
    }
    public function getImagesShowcaseAttribute()
    {
        return (new ProductDetailsService($this->id))->getProductMedias(true);
    }
    public function getViewsCountAttribute():int {
        return (new ProductDetailsService($this->id))->getViewsCount();
    }
    public function getSalesCountAttribute():int {
        return (new ProductDetailsService($this->id))->getSalesCount();
    }
    public function getStoreBalanceAttribute() {
        return StoreBalanceService::productStoreBalanceCalculator($this->id);
    }
    public function getRateAttribute(): string
    {
        return Cache::remember('product-comment-' . $this->id, 240, function () {
            $avg = $this->productComments()
                ->where('status', ProductComment::STATUS_APPROVED)
                ->whereRaw('id IN (SELECT MAX(id) FROM product_comments GROUP BY creator_id)')
                ->selectRaw('AVG(rate) AS avg')->first()->avg;
            return number_format($avg, 1);
        });
    }
    public function getMainImageAttribute($isAppend = true)
    {
        $media = $this->getFirstMedia('images');
        if ($media) {
            if ($isAppend)
                return $media->getFullUrl();
            else {
                $this->main_image = $media->getFullUrl();
                return;
            }
        }
        $varieties = $this->varieties;
        foreach ($varieties as $variety) {
            $media = $variety->getFirstMedia('images');
            if ($media) {
                if ($isAppend)
                    return $media->getFullUrl();
                else {
                    $this->main_image = $media->getFullUrl();
                    return;
                }
                //                return ($mediaResource) ? new MediaResource($media) : $media;
            }
        }
        if ($isAppend)
            return null;
        else
            $this->main_image = null;
    }
    public function getSpecificationsShowcaseAttribute() {
        return (new ProductDetailsService($this->id))->getSpecificationsShowcase();
    }
    public function getSizeChartsShowcaseAttribute() {
        return (new ProductDetailsService($this->id))->getSizeChartsShowcase();
    }

    public function getTotalSaleAmountAttribute()
    {
        return $this->orderItems->where('status', 1)->map(function($item) {
            return $item->amount * $item->quantity;
        })->sum();
    }


    // ATTRIBUTES ======================================================================================================
    // =================================================================================================================
    // =================================================================================================================
    // RELATIONS  ======================================================================================================
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_product');
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class)
            ->latest('order')
            ->using(ProductSpecificationPivot::class)
            ->withPivot('id', 'value', 'specification_value_id');
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function sizeCharts(): HasMany
    {
        return $this->hasMany(SizeChart::class);
    }
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    public function varieties(): HasMany
    {
        return $this->hasMany(Variety::class)->orderBy('order', 'DESC');
    }
    public function productComments(): HasMany
    {
        return $this->hasMany(ProductComment::class);
    }
    public function flashes(): BelongsToMany
    {
        return $this->belongsToMany(Flash::class)
            ->withPivot(['discount_type', 'discount', 'salable_max', 'sales_count']);
    }
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'favorites');
    }
    public function sets(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Product\Entities\ProductSet::class, 'product_set_product');
    }
    public function recommendations()
    {
        return $this->hasMany(\Modules\Product\Entities\Recommendation::class);
    }
    public function RecommendationItems()
    {
        return $this->hasMany(RecommendationItem::class);
    }
    public function gifts()
    {
        return $this->belongsToMany(Gift::class, 'gift_product_variety', 'product_id')
            ->withPivot('should_merge');
    }
    public function activeGifts()
    {
        return $this->gifts()->active();
    }
    /** برای سرچ
    /* شامل کتگوری فعلی پروداکت و پدرانش بطور بازگشتی
     * UNUSED
     */
    public function categoriesIndex()
    {
        return $this->belongsToMany(Category::class, 'category_product_index');
    }
    public function activeFlash() {
        return $this->flashes()->active()->latest()->whereColumn('sales_count', '<', 'salable_max');
    }
    // RELATIONS  ======================================================================================================
    // =================================================================================================================

    // =================================================================================================================
    // USED METHODS FOR PRODUCT OBJECT =================================================================================
    public function assignVariety($productRequest, $update = false)
    {
        if (!empty($varieties = $productRequest['varieties'])) {
            if ($update) {
                Variety::updateVarieties($varieties, $this);
            } else {
                Variety::storeVarieties($varieties, $this);
            }
        } else {
            Variety::storeFakeVariety($this, $productRequest['quantity']);
        }

    }
    public function assignSpecifications(array $product)
    {

        if (isset($product['specifications']) && !empty($product = $product['specifications'])) {
            $this->specifications()->detach();
            foreach ($product as $specification) {
                $value = $specification['value'];

                $specificationModel = Specification::whereId($specification['id'])->first();
                if ($specificationModel->type == 'select') {
                    #زمانی که انتخابی است value == id است
                    $specificationValueModel = SpecificationValue::find($value);
                    $this->specifications()
                        ->attach($specificationModel->id, ['specification_value_id' => $specificationValueModel->id]);
                } elseif ($specificationModel->type == 'text') {
                    $this->specifications()
                        ->attach($specificationModel->id, ['value' => $value]);
                } elseif ($specificationModel->type == 'multi_select') {
                    $this->specifications()
                        ->attach($specificationModel->id);

                    $productSpecificationPivot = $this->specifications()->where('specification_id', $specificationModel->id)
                        ->first()->pivot;

                    $productSpecificationPivot->specificationValues()->sync($value);

                }
            }
        }
    }
    public function assignSizeChart($product)
    {
        if (!empty($sizeChartsRequest = $product['size_charts'])) {
            SizeChart::storeSizeCharts($sizeChartsRequest, $this);
        } else {
            SizeChart::query()->where('product_id', $this->id)->delete();
        }
    }
    public function addImages($images): void
    {
        if (empty($images))
            return;
        Media::addMedia($images, $this, 'images');
    }
    public function updateImages($images): void
    {
        $updatedImages = Media::updateMedia($images, $this, 'images');
        $mediaToDelete = $this->media()->where('collection_name', 'images')->whereNotIn('id', $updatedImages)->get();
        foreach ($mediaToDelete as $media) {
            $media->delete();
        }
        $this->load('media');
    }
    public function loadVarietiesShowcase($varieties):void
    {
        $allAvailableVarietyIds = [];
        $colors = [];
        // create color section ========
        foreach ($varieties as $variety) {
            if (!$variety->color_showcase) continue;
            $existsFlag = false;

            for ($i=0;$i<count($colors);$i++) {
                if ($colors[$i]['name'] == $variety->color_showcase['name']) {
                    $existsFlag = true;
                    $colors[$i]['myAvailableVarietyIds'][] = $variety->id;
                    $allAvailableVarietyIds[] = $variety->id;
                }
            }
            if (!$existsFlag) {
                $colors[] = [
                    'id' => $variety->color_showcase['id'],
                    'name' => $variety->color_showcase['name'],
                    'code' => $variety->color_showcase['code'],
                    'myAvailableVarietyIds' => [$variety->id],
                ];
                $allAvailableVarietyIds[] = $variety->id;
            }
        }

        // create attributes section ======
        $attributes = [];
        foreach ($varieties as $variety) {
            foreach ($variety->attributes_showcase as $varietyAttribute) {
                // we check that this attribute exists or not
                $existsAttributeFlag = false;
                for ($i=0;$i<count($attributes);$i++) {
                    if ($attributes[$i]['name'] == $varietyAttribute['name']) {
                        // so this attribute exists.
                        $existsAttributeFlag = true;
                        // now we should check that this value exists or not
                        $existsValueFlag = false;
                        for ($j=0;$j<count($attributes[$i]['modelDetails']);$j++) {
                            if ($attributes[$i]['modelDetails'][$j]['value'] == $varietyAttribute['value']) {
                                // so this value exists
                                $existsValueFlag = true;
                                $attributes[$i]['modelDetails'][$j]['myAvailableVarietyIds'][] = $variety->id;
                                $allAvailableVarietyIds[] = $variety->id;
                            }
                        }
                        if (!$existsValueFlag) {
                            $attributes[$i]['modelDetails'][] = [
                                'value' => $varietyAttribute['value'],
                                'myAvailableVarietyIds' => [$variety->id],
                            ];
                            $allAvailableVarietyIds[] = $variety->id;
                        }
                    }
                }

                if (!$existsAttributeFlag) {
                    $attributes[] = [
                        'name' => $varietyAttribute['name'],
                        'label' => $varietyAttribute['label'],
                        'type' => $varietyAttribute['type'],
                        'style' => $varietyAttribute['style'],
                        'modelDetails' => [
                            [
                                'value' => $varietyAttribute['value'],
                                'myAvailableVarietyIds' => [$variety->id],
                            ]
                        ]
                    ];
                    $allAvailableVarietyIds[] = $variety->id;
                }
            }
        }

        $this->varieties_showcase = [
            'allAvailableVarietyIds' => $allAvailableVarietyIds,
            'colors' => $colors,
            'attributes' => $attributes,
        ];

        return ;



//        dd($colors, $attributes);

        // ========================================
        $output = [];
        $hasColor = false;
        if (count($colors) > 0) {
            $hasColor = true;
            // so color exists.
            foreach ($colors as $color) {
                $hereAttribute = null;
                $variety_id = $color['myAvailableVarietyIds'];
                $hasAttributeFlag = false;

                if (isset($attributes[0])) {
                    $hasAttributeFlag = true;
                    foreach ($attributes[0]['modelDetails'] as $modelDetail) {
                        $variety_id = $this->getIntersection($color['myAvailableVarietyIds'], $modelDetail['myAvailableVarietyIds']);
                        $hereAttribute[] = [
                            'name' => $attributes[0]['name'],
                            'label' => $attributes[0]['label'],
                            'type' => $attributes[0]['type'],
                            'style' => $attributes[0]['style'],
                            'value' => $modelDetail['value'],
                            'variety_id' => $variety_id,
                            'quantity' => 12
                        ];
                    }
                }
                if ($hasAttributeFlag) {
                    $output['colors'][] = [
                        'color_id' => $color['id'],
                        'name' => $color['name'],
                        'code' => $color['code'],
                        'attributes' => $hereAttribute
                    ];
                } else {
                    $variety_id = $color['myAvailableVarietyIds'][0];
                    $quantity = 12;
                    $output['colors'][] = [
                        'color_id' => $color['id'],
                        'name' => $color['name'],
                        'code' => $color['code'],
                        'variety_id' => $variety_id,
                        'quantity' => $quantity,
                        'attributes' => $hereAttribute
                    ];
                }
            }
        }

        $hereAttribute = [];
        if (isset($attributes[0])) {
            if (!isset($attributes[1])) {
                // so it has one single attribute.
                if ($hasColor) {
                    // so there is one color and one attribute
                    foreach ($attributes[0]['modelDetails'] as $modelDetail) {
                        $hereColors = [];
                        foreach ($colors as $color) {
                            $variety_id = $this->getIntersection($color['myAvailableVarietyIds'], $modelDetail['myAvailableVarietyIds']);
                            $quantity = 11;
                            $hereColors[] = [
                                'color_id' => $color['id'],
                                'name' => $color['name'],
                                'code' => $color['code'],
                                'variety_id' => $variety_id,
                                'quantity' => $quantity
                            ];
                        }
                        $output['attributes'][] = [
                            'name' => $attributes[0]['name'],
                            'label' => $attributes[0]['label'],
                            'type' => $attributes[0]['type'],
                            'style' => $attributes[0]['style'],
                            'value' => $modelDetail['value'],
                            'colors' => $hereColors
                        ];
                    }
                } else {
                    // so there is no color. and it has one single attribute
                    foreach ($attributes[0]['modelDetails'] as $modelDetail) {
                        $variety_id = $modelDetail['myAvailableVarietyIds'][0];
                        $quantity = 11;
                        $output['attributes'][] = [
                            'name' => $attributes[0]['name'],
                            'label' => $attributes[0]['label'],
                            'type' => $attributes[0]['type'],
                            'style' => $attributes[0]['style'],
                            'value' => $modelDetail['value'],
                            'variety_id' => $variety_id,
                            'quantity' => $quantity,
                        ];
                    }
                }
            } else {
                // there is two attributes. so we don't consider color.
                // first =========
                foreach ($attributes[0]['modelDetails'] as $firstModelDetail) {
                    $hereAttributes = [];
                    foreach ($attributes[1]['modelDetails'] as $secondModelDetail) {
                        $variety_id = $this->getIntersection($firstModelDetail['myAvailableVarietyIds'], $secondModelDetail['myAvailableVarietyIds']);
                        $quantity = 55;
                        $hereAttributes[] = [
                            'name' => $attributes[1]['name'],
                            'label' => $attributes[1]['label'],
                            'type' => $attributes[1]['type'],
                            'style' => $attributes[1]['style'],
                            'value' => $secondModelDetail['value'],
                            'variety_id' => $variety_id,
                            'quantity' => $quantity,
                        ];
                    }
                    $output['attributes'][0][] = [
                        'name' => $attributes[0]['name'],
                        'label' => $attributes[0]['label'],
                        'type' => $attributes[0]['type'],
                        'style' => $attributes[0]['style'],
                        'value' => $firstModelDetail['value'],
                        'attributes' => $hereAttributes
                    ];
                }

                // second =========
                foreach ($attributes[1]['modelDetails'] as $secondModelDetail) {
                    $hereAttributes = [];
                    foreach ($attributes[0]['modelDetails'] as $firstModelDetail) {
                        $variety_id = $this->getIntersection($secondModelDetail['myAvailableVarietyIds'], $firstModelDetail['myAvailableVarietyIds']);
                        $quantity = 57;
                        $hereAttributes[] = [
                            'name' => $attributes[0]['name'],
                            'label' => $attributes[0]['label'],
                            'type' => $attributes[0]['type'],
                            'style' => $attributes[0]['style'],
                            'value' => $firstModelDetail['value'],
                            'variety_id' => $variety_id,
                            'quantity' => $quantity,
                        ];
                    }
                    $output['attributes'][1][] = [
                        'name' => $attributes[1]['name'],
                        'label' => $attributes[1]['label'],
                        'type' => $attributes[1]['type'],
                        'style' => $attributes[1]['style'],
                        'value' => $secondModelDetail['value'],
                        'attributes' => $hereAttributes
                    ];
                }


            }

        }


        $this->varieties_showcase = [
            'colors' => isset($output['colors']) ? $output['colors'] : null,
            'attributes' => isset($output['attributes']) ? $output['attributes'] : null,
        ];


        /// OLD ========================================
        /*$colors = [];
        // create color section ========
        foreach ($varieties as $variety) {
            if (!$variety->color_showcase) continue;
            $existsFlag = false;

            for ($i=0;$i<count($colors);$i++) {
                if ($colors[$i]['name'] == $variety->color_showcase['name']) {
                    $existsFlag = true;
                    $colors[$i]['myAvailableVarietyIds'][] = $variety->id;
                }
            }
            if (!$existsFlag) {
                $colors[] = [
                    'name' => $variety->color_showcase['name'],
                    'myAvailableVarietyIds' => [$variety->id],
                ];
            }
        }
        // create attributes section ======
        $attributes = [];
        foreach ($varieties as $variety) {
            foreach ($variety->attributes_showcase as $varietyAttribute) {
                // we check that this attribute exists or not
                $existsAttributeFlag = false;
                for ($i=0;$i<count($attributes);$i++) {
                    if ($attributes[$i]['name'] == $varietyAttribute['name']) {
                        // so this attribute exists.
                        $existsAttributeFlag = true;
                        // now we should check that this value exists or not
                        $existsValueFlag = false;
                        for ($j=0;$j<count($attributes[$i]['modelDetails']);$j++) {
                            if ($attributes[$i]['modelDetails'][$j]['value'] == $varietyAttribute['value']) {
                                // so this value exists
                                $existsValueFlag = true;
                                $attributes[$i]['modelDetails'][$j]['myAvailableVarietyIds'][] = $variety->id;
                            }
                        }
                        if (!$existsValueFlag) {
                            $attributes[$i]['modelDetails'][] = [
                                'value' => $varietyAttribute['value'],
                                'myAvailableVarietyIds' => [$variety->id],
                            ];
                        }
                    }
                }

                if (!$existsAttributeFlag) {
                    $attributes[] = [
                        'name' => $varietyAttribute['name'],
                        'label' => $varietyAttribute['label'],
                        'type' => $varietyAttribute['type'],
                        'style' => $varietyAttribute['style'],
                        'modelDetails' => [
                            [
                                'value' => $varietyAttribute['value'],
                                'myAvailableVarietyIds' => [$variety->id],
                            ]
                        ]
                    ];
                }
            }
        }*/
    }
    private function getIntersection($array1, $array2){
        $intersection = array_values(array_intersect($array1,$array2));
        if (count($intersection) > 1)
            return $intersection;
        elseif (count($intersection) == 0)
            return null;
        else
            return $intersection[0];
    }
    public function isAvailable() {
        return in_array($this->status, [static::STATUS_AVAILABLE]);
    }
    public function hasFakeVariety() {
        foreach ($this->varieties as $variety) {
            if ($variety->isFake()) {
                return true;
            }
        }

        return false;
    }
    public static function getStatusCounts()
    {
        $counts = [];
        foreach (static::getAvailableStatuses() as $status) {
            $counts[$status] = static::query()->where('status', $status)->count();
        }

        return $counts;
    }
    public function load_gifts()
    {
        $majorGifts = collect();
        $varieties = $this->varieties;
        foreach ($this->activeGifts as $activeGift) {
            $majorGifts->push($activeGift);
        }
        foreach ($varieties as $variety) {
            $gifts = $variety->activeGifts;
            foreach ($gifts as $gift) {
                $majorGifts->push($gift);
            }
        }

        $this->gitfs = $majorGifts->unique();
    }
    // it might this method don't use.
    public static function checkStatusChanges($requestProduct)
    {
        $varieties = $requestProduct['varieties'] ?? null;
        if (!empty($varieties)) {
            $hasAnyVariety = false;
            foreach ($varieties as $variety) {
                if (($variety['quantity'] != 0)) {
                    $hasAnyVariety = true;
                }
            }
            if (!$hasAnyVariety && ($requestProduct['status'] == static::STATUS_AVAILABLE)) {
                throw new \Exception('زمانی که موجودی محصول صفر است نمیتواند وضعیت آن موجود باشد');
            }
        } else {
            if ($requestProduct['quantity'] == 0 && $requestProduct['status'] == static::STATUS_AVAILABLE) {
                throw new \Exception('زمانی که موجودی محصول صفر است نمیتواند وضعیت آن موجود باشد');
            }
        }
    }
    // USED METHODS FOR PRODUCT OBJECT =================================================================================
    // =================================================================================================================

    // =================================================================================================================
    // DEFAULT METHODS FOR PRODUCT OBJECT ==============================================================================
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }
    public static function getAvailableStatuses(): array
    {
        return [
            static::STATUS_AVAILABLE,
            static::STATUS_OUT_OF_STOCK,
            static::STATUS_SOON,
            static::STATUS_DRAFT,
            static::STATUS_AVAILABLE_OFFLINE
        ];
    }

    public static function getAvailableDiscountTypes(): array
    {
        return [static::DISCOUNT_TYPE_FLAT, static::DISCOUNT_TYPE_PERCENTAGE];
    }

    // DEFAULT METHODS FOR PRODUCT OBJECT ==============================================================================
    // =================================================================================================================


    //    protected $with = ['activeFlash'];

    //    protected static $commonRelations = [
//        /*'varieties.attributes.pivot.attributeValue', 'varieties.color','categories',
//        'specifications.pivot.specificationValues', 'specifications.pivot.specificationValue',
//        'brand', 'sizeCharts', 'unit', 'tags', 'activeFlash'*/
//    ];

    //    protected $appends = ['images', 'total_quantity', 'price', 'rate',
//        'major_discount_amount', 'major_image', 'major_gifts',
//        'major_final_price','views_count',
//        'max_discount_variety','major_variety_price','minor_variety_price'
//    ];

    // =================================================================================================================
    // UNUSABLE OLD METHODS ============================================================================================
    public function prettyVariety(): HasMany
    {
        return $this->hasMany(PrettyVariety::class);
    }
    public function getTotalQuantityAttribute()
    {
        if (!$this->relationLoaded('varieties')) {
            $this->makeHidden('total_quantity');
            return new DontAppend('getTotalQuantityAttribute 1');
        }
        $balance = 0;
        $varieties = $this->varieties;
        foreach ($varieties as $variety) {
            if (!$variety->relationLoaded('store')) {

                return new DontAppend('getTotalQuantityAttribute 2');
            }
            $balance += $variety->store->balance ?? 0;
        }
        return $balance;
    }
    public function getTotalSalesAttribute()
    {
        if (!$this->relationLoaded('varietyOnlyIdsRelationship')) {
            return new DontAppend('Product getTotalSalesAttribute');
        }
        $varietyIds = $this->varietyOnlyIdsRelationship->pluck('id');
        return OrderItem::query()->whereIn('variety_id', $varietyIds)
            ->whereHas('variety', function ($q) {
                $q->where('product_id', $this->id);
            })->sum('quantity');
    }
    public function varietyOnlyIdsRelationship()
    {
        return $this->varieties()->select('id');
    }
    public function getMostDiscountAttribute()
    {
        if (!$this->relationLoaded('varietyOnlyDiscountsRelationship')) {
            return new DontAppend('Product getMostDiscountsAttribute');
        }
        $discount = 0;
        $varieties = $this->varietyOnlyDiscountsRelationship;
        foreach ($varieties as $variety) {
            if ($variety->final_price['discount_price'] > $discount) {
                $discount = $variety->final_price['discount_price'];
            }
        }
        return $discount;
    }
    public function varietyOnlyDiscountsRelationship()
    {
        return $this->varieties()->select(
            'varieties.id',
            'varieties.product_id',
            'varieties.discount',
            'varieties.discount_type',
            'varieties.price'
        )
            ->with('product.activeFlash');
    }
    public function getTotalFavoriteAttribute()
    {
        if (!$this->relationLoaded('favorite')) {
            return new DontAppend('favorite');
        }
        return $this->favorite->count();
    }
    public function getSlugAttribute()
    {
        if (
            isset($this->attributes['slug'])
            && !empty($this->attributes['slug'])
        ) {
            return $this->attributes['slug'];
        }
        if (!$this->title) {
            return;
        }
        return Str::slug($this->title);
    }
    public function setPublishedAtAttribute($date)
    {
        if ($date == null)
            return;

        $carbonDate = is_numeric($date) ? Carbon::createFromTimestamp($date)->toDateTimeString() : $date;
        $this->attributes['published_at'] = $carbonDate;
    }
    public function scopeHasCategory($query, $id)
    {
        $query->whereHas('categories', function ($q) use ($id) {
            $q->where('categories.id', $id);
        });
    }
    public function getMajorGiftsAttribute()
    {
        if (!$this->relationLoaded('varieties.activeGifts')) {
            return new DontAppend('getMajorGiftsAttribute');
        }
        $majorGifts = collect();
        $varieties = $this->varieties;
        foreach ($this->activeGifts as $activeGift) {
            $majorGifts->push($activeGift);
        }
        foreach ($varieties as $variety) {
            $gifts = $variety->activeGifts;
            foreach ($gifts as $gift) {
                $majorGifts->push($gift);
            }
        }

        return $majorGifts->unique();
    }
    public static function makeHiddenForFront(Product $product, $other = [])
    {
        $product->makeHidden([
            'creatorable_id',
            'creatorable_type',
            'approved_at',
            'published_at',
            'unit_price',
            'purchase_price',
            'updaterable_id',
            'updaterable_type',
            ...$other
        ]);
    }
//    public function setDiscountTypeAttribute($discountType) {
//        if ($discountType != null && !in_array($discountType, static::getAvailableDiscountTypes())) {
//            throw Helpers::makeValidationException('نوع تخفیف وارد شده نامعتبر است');
//        }
//        $this->attributes['discount_type'] = $discountType;
//    }

    public function setStatusAttribute($status)
    {
        if (!in_array($status, static::getAvailableStatuses())) {
            return throw Helpers::makeValidationException('وضعیت انتخاب شده نامعتبر است');
        }

        $this->attributes['status'] = $status;
    }

    public function setBarcodeAttribute($value)
    {
        $this->attributes['barcode'] = Helpers::convertFaNumbersToEn($value);
    }

    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = Helpers::convertFaNumbersToEn($value);
    }


    //    /**
//     * @param $eventName
//     * @return string
//     */
//    public function __construct()
//    {
//        parent::__construct($attributes);
//        $withSetting = app(CoreSettings::class)->get('product.with');
//        $gift = app(CoreSettings::class)->get('product.gift.active');
//        if (!empty($withSetting)) {
//            $this->with = array_unique(array_merge($this->with, $withSetting));
//        }
//        if ($gift) {
//            if (auth()->user() instanceof Admin) {
//                $this->with = array_merge($this->with,['activeGifts', 'gifts']);
//            }else{
//                $this->with = array_merge($this->with,['activeGifts']);
//            }
//        }
//    }

    //    public function getActivitylogOptions(): LogOptions
//    {
//        $admin = \Auth::user() ?? Admin::query()->first();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Product')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "محصول {$this->title} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

    public function getMajorVarietyPriceAttribute()
    {
        if (!$this->relationLoaded('varieties')) {
            return new DontAppend('getMajorDiscountPercentageAttribute');
        }
        $major_price = 0;
        foreach ($this->varieties as $variety) {
            if ($variety->quantity != 0 && $variety->price > $major_price) {
                $major_price = $variety->price;
            }
        }
        return $major_price;
    }
    public function getMinorVarietyPriceAttribute()
    {
        if (!$this->relationLoaded('varieties')) {
            return new DontAppend('getMajorDiscountPercentageAttribute');
        }
        $minor_price = PHP_INT_MAX;
        foreach ($this->varieties as $variety) {
            if ($variety->quantity != 0 && $variety->price < $minor_price) {
                $minor_price = $variety->price;
            }
        }
        return $minor_price;
    }
    public function getMajorImageAttribute()
    {
        if (!$this->relationLoaded('varieties')) {
            return new DontAppend('getMajorImageAttribute');
        }
        $varieties = $this->varieties;

        $firstMedia = ($headVariety = $varieties->where('is_head', 1)?->first())
            ? $headVariety->main_image
            : $this->getFirstMedia('images');

        if ($firstMedia) {
            return new MediaResource($firstMedia);
        }

        foreach ($varieties as $variety) {
            if ($variety->quantity != 0) {
                $media = $variety->main_image;
                if ($media) {
                    return new MediaResource($media);
                }
            }
        }

        return $this->getMainImageAttribute();
    }
    public function getMajorDiscountAmountAttribute()
    {
        if (!$this->relationLoaded('varieties')) {
            return new DontAppend('getMajorDiscountAmount');
        }
        $finalPrice = PHP_INT_MAX;
        $discount = 0;
        foreach ($this->varieties as $variety) {
            if ($variety->quantity != 0 && $variety->is_head == 1) {
                $discount = $variety->final_price['discount_price'];
                break;
            }
            if (
                $variety->quantity != 0 &&
                $variety->final_price['amount'] < $finalPrice
            ) {
                $finalPrice = $variety->final_price['amount'];
                $discount = $variety->final_price['discount_price'];
            }
        }

        return $discount;
    }
    public function getMaxDiscountVarietyAttribute()
    {
        $max = 0;
        $max_type = '';
        foreach ($this->varieties as $variety) {
            if ($variety->discount != null) {
                if ($variety->discount_type == 'percentage' && $variety->discount) {
                    if ($variety->discount > $max) {
                        $max = $variety->discount;
                        $max_type = $variety->discount_type;
                    }
                } elseif ($variety->discount_type == 'flat' && $variety->discount) {
                    if ($variety->discount > $max) {
                        $max = $variety->discount;
                        $max_type = $variety->discount_type;
                    }
                }

                return $max_discount_variety = [
                    'type' => $max_type,
                    'value' => $max,
                ];
            }
        }
    }


    public static function sortOrders(Model $model, int $order): void
    {
        $id = $model->id;
        $oldOrder = $model->order;
        $orders = [];
        $orderedServices = $model->query()->where('order', '!=', null)->ordered()->where('id', '!=', $id)->get(['id', 'order']);

        if ($order < $oldOrder) {
            $beforeOrders = $orderedServices->where('order', '<', $order)->pluck('id')->all();
            $orders = $beforeOrders;
            $orders[] = $id;
            $afterOrders = $orderedServices->where('order', '>=', $order)->pluck('id')->all();
            $orders = array_merge($orders, $afterOrders);

        } elseif ($order > $oldOrder) {
            $beforeOrders = $orderedServices->where('order', '<=', $order)->pluck('id')->all();
            $orders = $beforeOrders;
            $orders[] = $id;
            $afterOrders = $orderedServices->where('order', '>', $order)->pluck('id')->all();
            $orders = array_merge($orders, $afterOrders);
        }

        if (count($orders) > 0) {
            $model->setNewOrder($orders);
        }
    }
    public static function getMaxOrder(): int
    {
        return (int) self::query()->max('order') + 1;
    }
    public static function getMinOrder(): int
    {
        return (int) self::query()->min('order') - 1;
    }

// is getStatusLabelColorAttribute method usable?
    public static function getStatusLabelColorAttribute($status)
    {
        return match ($status) {
            self::STATUS_DRAFT => 'primary',
            self::STATUS_SOON => 'secondary',
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_OUT_OF_STOCK => 'danger',
            self::STATUS_AVAILABLE_OFFLINE => 'success',
        };
    }
}

