<?php

namespace Modules\Product\Services;
/*
 | SERVICE DUTIES:
 |
 |     MEDIA
 |          getProductMedias
 |          getVarietyMedias
 |          getVarietyMainImage
 |     SPECIFICATIONS
 |          getSpecificationsShowcase
 |     SIZE_CHARTS
 |          getSizeChartsShowcase
 |     FINAL_PRICE
 |          getProductFinalPrice
 |          getVarietyFinalPrice
 |     VIEWS_COUNT
 |          getViewsCount
 |     SALES_COUNT
 |          getSalesCount
 |     VARIETIES DETAILS
 |          getVarietyColorShowcase
 |          getVarietyAttributesShowcase
 |          getVarietyTitle
 |
 |
 |
 |
 |
 |
 |
 |
 | cache structure:
 | [
 |     "media" => [
 |         "main_image" => {},
 |         "images" => [{},{}],
 |         "varieties" => ['variety_id' => [{},{},{}], 'variety_id' => [{},{},{}]]
 |     ],
 |     "specifications" => [,
 |     "sizeCharts" => [],
 |     "productFinalPrice" => [],
 |     "varietiesFinalPrice" => [],
 |     "views_count" => [],
 |     "sales_count" => [],
 |     "varietiesDetails" => [
 |          <variety_id> => [
 |              color => [],
 |              attributes => [
 |                  "size" => "XL"
 |                  "tarh" => "گل"
 |              ],
 |              title => 'string'
 |          ],
 |
 |      ],
 |
 | {} =====> this is object of MediaDisplay class
 | [] =====> this is array
 |
 | we have created this class to get all images of a product and its varieties from cache
 | and we don't create it again. so we have appended images of products and varieties using images_showcase append
 |
 * */




use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\SizeChart\Entities\SizeChart;
use Modules\Specification\Entities\Specification;
/* todo: we have to create a new structure for attributes of all varieties */
class ProductDetailsService extends CacheServiceInterface
{
    public static array $usedModelsInCache = [
        Product::class,
        Variety::class,
    ];

    protected function constructNeedId(): bool { return true;}
    public function cacheCreator($product_id): void
    {
        // MEDIA ===================================
        $product = Product::findOrFail($product_id);
        $firstMedia = $product->getFirstMedia('images');
        if ($firstMedia) {
            $firstMediaUrl = MediaDisplay::objectCreator($firstMedia);
        } else {
            foreach ($product->varieties as $variety) {
                $media = $variety->getFirstMedia('images');
                if ($media) {
                    $firstMediaUrl = MediaDisplay::objectCreator($media);
                    break;
                }else{
                    $firstMediaUrl = null;
                }
            }
        }
        $this->cacheData['media']['main_image'] = $firstMediaUrl;
        if (!$firstMedia) {
            // so there wasn't any image for this product. I want to response empty array
            $this->cacheData['media']['images'] = [];
        } else {
            // we don't set the first image of this product in images attribute again.
            $productMedias = $product->media()->where('id', '!=',$firstMedia->id)->get();
            // if the product has just one image we should respond empty array for images attribute.
            if ($productMedias->count() == 0) $this->cacheData['media']['images'] = [];
            foreach ($productMedias as $productMedia) {
                $this->cacheData['media']['images'][] = MediaDisplay::objectCreator($productMedia);
            }
        }
        // we also put varieties' images in cache
        foreach ($product->varieties as $variety) {
            $varietyMedias = $variety->getMedia('images');
            foreach ($varietyMedias as $varietyMedia) {
                $this->cacheData['media']['varieties'][$variety->id][] = MediaDisplay::objectCreator($varietyMedia);
            }

        }

        // SPECIFICATION ===================================
        $product->load(['specifications.pivot.specificationValues','specifications.pivot.specificationValue']);
        $this->cacheData['specifications'] = [];

        foreach ($product->specifications as $specification) {
            switch ($specification->type)
            {
                case Specification::TYPE_TEXT:
                    $this->cacheData['specifications'][$specification->label] = $specification->pivot->value;
                    break;
                case Specification::TYPE_SELECT:
                    $this->cacheData['specifications'][$specification->label] = $specification->pivot->specificationValue->value;
                    break;
                case Specification::TYPE_MULTI_SELECT:
                    $multiSelectValues = [];
                    foreach ($specification->pivot->specificationValues as $specificationValue) {
                        $multiSelectValues[] = $specificationValue->value;
                    }
                    $this->cacheData['specifications'][$specification->label] = $multiSelectValues;
            }
        }
        // SIZE_CHART ======================================
        $product->load('sizeCharts.type.values');
        $this->cacheData['sizeCharts'] = [];
        foreach ($product->sizeCharts as $sizeChart) {
            $charts = json_decode($sizeChart->chart);
            $chartsShowcase = [];

            for ($j = 0; $j < count($charts[0]); $j++) {
                for ($i = 1; $i < count($charts); $i++) {
                    $chartsShowcase[$charts[0][$j]][] = $charts[$i][$j];
                }
            }
            $this->cacheData['sizeCharts'][] = [
                'title' => $sizeChart->title,
                'rows' => $chartsShowcase
            ];
        }

        // FINAL_PRICE ======================================
        $this->cacheData['productFinalPrice'] = $this->productFinalPriceCalculator($product);

        $activeVarietiesIds = Variety::query()->where('product_id', $product->id)->whereNull('deleted_at')->pluck('id')->toArray();
        $varieties = Variety::find($activeVarietiesIds);
        foreach ($varieties as $variety)
            $this->cacheData['varietiesFinalPrice'][$variety->id] = $this->varietyFinalPriceCalculator($product, $variety);
        // VIEWS_COUNT =====================================
        $this->cacheData['views_count'] = (int)views($product)->count();
        // SALES_COUNT =====================================
        $this->cacheData['sales_count'] = (int)OrderItem::query()
            ->select(DB::raw('SUM(quantity) as sales_count'))
            ->where('product_id', $product_id)
            ->where('status',1)->first()->sales_count;
        // VARIETIES DETAILS ===============================
        foreach ($varieties as $variety) {
            $color = null;
            if ($variety->color) {
                $color = [
                    'id' => $variety->color->id,
                    'name' => $variety->color->name,
                    'code' => $variety->color->code,
                ];
            }
            $this->cacheData['varietiesDetails'][$variety->id]['color'] = $color;

            $allAttributes = [];
            foreach ($variety->attributes as $attribute) {
                $allAttributes[] = [
                    'name' => $attribute->name,
                    'label' => $attribute->label,
                    'type' => $attribute->type,
                    'style' => $attribute->style,
                    'value' => $attribute->pivot->value,
                ];
            }
            $this->cacheData['varietiesDetails'][$variety->id]['attributes'] = $allAttributes;
            // variety.title
            $title = $product->title ;
            if ($variety->color) $title .= (' ' . $variety->color->name);
//            $title .= $variety->color->name ?? '';

            foreach ($variety->attributes ?? [] as $attribute) {
                $title .= ' | '.$attribute->label.': '.$attribute->pivot->value;
            }
            $this->cacheData['varietiesDetails'][$variety->id]['title'] = $title;
        }
        // end creator ======================================
        Cache::forever(self::getCacheName($product_id), $this->cacheData);
    }
//// ===================================================================================================================
//// ===================================================================================================================
//// ===================================================================================================================
//// ===================================================================================================================

//// MEDIA =============================================================================================================
    public function getProductMedias(bool $all_images = false):array {
        $productMediaCache = $this->cacheData['media'];
        if (!request()->has('withAllProductImages') || !request('withAllProductImages'))
            $productMediaCache['varieties'] = [];
        return $productMediaCache;
    }
    public function getVarietyMedias($variety_id): ?array  {
        $productMediaCache = $this->cacheData['media'];
        return $productMediaCache['varieties'][$variety_id] ?? null;
    }
    public function getVarietyMainImage($variety_id): null|MediaDisplay  {
        if (isset($this->cacheData['media']['varieties'][$variety_id][0]) && $this->cacheData['media']['varieties'][$variety_id][0])
            return $this->cacheData['media']['varieties'][$variety_id][0];
        // default is main_image of product
        return $this->cacheData['media']['main_image'];
    }
//// SPECIFICATIONS ====================================================================================================
    public function getSpecificationsShowcase() {
        return $this->cacheData['specifications'];
    }
//// SIZE_CHARTS =======================================================================================================
    public function getSizeChartsShowcase() {
        return $this->cacheData['sizeCharts'];
    }
//// FINAL_PRICE =======================================================================================================
    public function getProductFinalPrice() {
        /* todo: we should return price for person itself. */
        // it means if it was colleague we should set colleague's price in product final_price
        return $this->cacheData['productFinalPrice'];
    }
    public function getVarietyFinalPrice($variety_id) {
        /* todo: we should return price for person itself. */
        // it means if it was colleague we should set colleague's price in product final_price
        return $this->cacheData['varietiesFinalPrice'][$variety_id];
    }
    private function productFinalPriceCalculator($product):array {
        $appliedDiscountType = 'none';
        $discountType = 'none';

        $flash = $product->activeFlash->first();
        $discount = 0;

        if ($flash) {
            $discount = $flash->discount;
            $discountType =  $flash->discount_type;
            $appliedDiscountType = 'flash';
        } elseif (!in_array($product->discount, [0, '0', null])  && $product->discount_until >= now()) {
            $discount = $product->discount;
            $discountType = $product->discount_type;
            $appliedDiscountType = 'product';
        }
        // discount on variety is not important here.
        $final_price = $product->unit_price;
        $discount_price = 0;
        $discount_colleague_price = 'none';
        $final_colleague_price = $product->colleague_price ?? 'none';
        // calculate discount_price and discount_colleague_price.
        if ($discount != 0) {
            if ($discountType == 'percentage') {
                $discount_price = (int)round(($discount * $product->unit_price) / 100);
            } else {
                $discount_price = $discount;
            }
            // calculate final_price
            $final_price = $product->unit_price - $discount_price;

            // colleague_price should define in product to calculate.
            if ($product->colleague_price) {
                if ($discountType == 'percentage') {
                    $discount_colleague_price = (int)round(($discount * $product->colleague_price) / 100);
                } else {
                    $discount_colleague_price = $discount;
                }
                // calculate final_colleague_price.
                $final_colleague_price = $product->colleague_price - $discount_colleague_price;
                // if colleague price with discount was less than purchase price we sell by final_price of customers price
                if ($product->purchase_price && $final_colleague_price < $product->purchase_price)
                    $final_colleague_price = $final_price;
            }
        }
        return [
            'base_amount' => $product->unit_price,
            'base_colleague_amount' => $product->colleague_price ?? 'none',
            'discount_model'  => $appliedDiscountType,
            'discount_type'  => $discountType,
            'discount'  => $discount,
            'discount_price' => $discount_price,
            'discount_colleague_price' => $discount_colleague_price,
            'colleague_amount' => $final_colleague_price,
            'amount' => $final_price
        ];
    }
    private function varietyFinalPriceCalculator($product, $variety) :array{
        $appliedDiscountType = 'none';
        $discountType = 'none';
        //        $flash = $this->product->activeFlash->first();
        $flash = $product->activeFlash->first();
        $discount = 0;

        if ($flash) {
            $discount = $flash->discount;
            $discountType =  $flash->discount_type;
            $appliedDiscountType = 'flash';
        } elseif ($variety->discount && $variety->discount_until >= now()) {
            $discount = $variety->discount;
            $discountType = $variety->discount_type;
            $appliedDiscountType = 'variety';
        }
        // discount on variety is not important here.
        $final_price = $variety->price;
        $discount_price = 0;
        $discount_colleague_price = 'none';
        $final_colleague_price = $variety->colleague_price ?? 'none';
        // calculate discount_price and discount_colleague_price.
        if ($discount != 0) {
            if ($discountType == 'percentage') {
                $discount_price = (int)round(($discount * $variety->price) / 100);
            } else {
                $discount_price = $discount;
            }
            // calculate final_price
            $final_price = $variety->price - $discount_price;

            // colleague_price should define in product to calculate.
            if ($variety->colleague_price) {
                if ($discountType == 'percentage') {
                    $discount_colleague_price = (int)round(($discount * $variety->colleague_price) / 100);
                } else {
                    $discount_colleague_price = $discount;
                }
                // calculate final_colleague_price.
                $final_colleague_price = $variety->colleague_price - $discount_colleague_price;
                // if colleague price with discount was less than purchase price we sell by final_price of customers price
                if ($variety->purchase_price && $final_colleague_price < $variety->purchase_price)
                    $final_colleague_price = $final_price;
            }
        }
        return [
            'base_amount' => $variety->price,
            'base_colleague_amount' => $variety->colleague_price ?? 'none',
            'discount_model'  => $appliedDiscountType,
            'discount_type'  => $discountType,
            'discount'  => $discount,
            'discount_price' => $discount_price,
            'discount_colleague_price' => $discount_colleague_price,
            'colleague_amount' => $final_colleague_price,
            'amount' => $final_price
        ];
    }
//// VIEWS_COUNT =======================================================================================================///
    public function getViewsCount():int {
        return $this->cacheData['views_count'];
    }
//// VIEWS_COUNT =======================================================================================================///
    public function getSalesCount():int {
        return $this->cacheData['sales_count'];
    }
//// VARIETIES DETAILS =================================================================================================///
    public function getVarietyColorShowcase($variety_id):array|null {
        return $this->cacheData['varietiesDetails'][$variety_id]['color'];
    }
    public function getVarietyAttributesShowcase($variety_id):array|null {
        return $this->cacheData['varietiesDetails'][$variety_id]['attributes'];
    }
    public function getVarietyTitle($variety_id):string {
        return $this->cacheData['varietiesDetails'][$variety_id]['title'];
    }
}
