<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Category\Entities\Category;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Entities\CategoryProductSort;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;

/*
 | SERVICE DUTIES.
 |      PRODUCTS_IDS AND VARIETY_IDS => these methods use to get products and varieties without load from database
 |          getActiveVarietiesIds
 |          getNotActiveVarietiesIds
 |          getAllVarietiesIds
 |          isVarietyIdActive
 |          getVarietiesCollection
 |          getProductObjectFromVarietyId
 |          getProductObjectFromProductId
 |          getVarietyObjectFromVarietyId
 |      SORT SYSTEM
 |          sortSystem => this is main sort system. we use it all over the project.
 |          getSortList => it just uses to get sortLists.
 |      MINIMUM AND MAXIMUM PRICE OF PRODUCTS COLLECTION
 |          getMinAndMaxPrice
 |
 |
 |
 | CACHE STRUCTURE:
 | [
 |     "productsCollection" => collection of all products,
 |     "varietiesCollection" => collection of all varieties,
 |     "productsSortedList" => [
 |          "byCategory" => array of productIds
 |          "mostVisited" => array of productIds
 |          "mostSales" => array of productIds
 |          "max_price" => array of productIds
 |          "min_price" => array of productIds
 |          "mostDiscount" => array of productIds
 |      ],
 | ]
 |
 * */



class ProductsCollectionService extends CacheServiceInterface
{
    public static array $usedModelsInCache = [
        Product::class,
        Variety::class,
        CategoryProductSort::class,
    ];

    protected function constructNeedId(): bool { return false; }
    public function cacheCreator($model_id = null) :void {
        $this->cacheData['productsCollection'] = Product::query()->select(['id','title','status','slug','free_shipping', 'short_description'])->get();
        $this->cacheData['varietiesCollection'] = Variety::query()->withoutGlobalScopes()->select(['id','product_id','name','description','deleted_at','color_id'])->get();
        $this->cacheData['productsSortedList'] = $this->sortListsCreator();

        Cache::forever(self::getCacheName(), $this->cacheData);
    }
    // CACHE CREATOR =======================================================================
    // =====================================================================================
    // PRODUCTS_IDS AND VARIETY_IDS =======================================================
    public function getActiveVarietiesIds($product_id) {
        $allVarieties = $this->cacheData['varietiesCollection'];
        return $allVarieties->whereNull('deleted_at')->where('product_id', $product_id)->values()->pluck('id')->toArray();
//        return $this->cacheData[$product_id]['active'];
    }
    public function getNotActiveVarietiesIds($product_id) {
        $allVarieties = $this->cacheData['varietiesCollection'];
        return $allVarieties->whereNotNull('deleted_at')->where('product_id', $product_id)->values()->pluck('id')->toArray();
//        return $this->cacheData[$product_id]['notActive'];
    }
    public function getAllVarietiesIds($product_id) {
        $allVarieties = $this->cacheData['varietiesCollection'];
        return $allVarieties->where('product_id', $product_id)->values()->pluck('id')->toArray();
//        return $this->cacheData[$product_id]['all'];
    }
    public function isVarietyIdActive($variety_id):bool {
        $variety = ($this->cacheData['varietiesCollection'])->where('id', $variety_id)->first()->deleted_at;
        if (!$variety)
            return true;
        return false;
    }
    public function getProductsCollection() {
        return $this->cacheData['productsCollection'];
    }
    public function getVarietiesCollection() {
        return $this->cacheData['varietiesCollection'];
    }
    public function getProductObjectFromVarietyId($variety_id): Product {
        $product_id = ($this->cacheData['varietiesCollection'])->where('id', $variety_id)->first()->product_id;
        return ($this->cacheData['productsCollection'])->where('id', $product_id)->first();
    }
    public function getProductObjectFromProductId($product_id): Product {
        return ($this->cacheData['productsCollection'])->where('id', $product_id)->first();
    }
    public function getVarietyObjectFromVarietyId($variety_id): Variety {
        return ($this->cacheData['varietiesCollection'])->where('id', $variety_id)->first();
    }
    // =====================================================================================
    // SORT SYSTEM =========================================================================
    public function sortSystem($products)
    {
        // this sortSystem does not use for search method.
        $allSortTypes = [
            'byCategory',
            'mostVisited',
            'mostSales',
            'max_price',
            'min_price',
            'mostDiscount'
        ];
        $sortBy = (request('sortBy') && in_array(request('sortBy'), $allSortTypes)) ? request('sortBy') : 'newest';
        if (request()->has('category_id') && request('category_id'))
            $sortBy = "byCategory";

        $statusSort = [
            'available' => 1, /* products with status=available comes first it depends on number. this number must start from one. not zero. */
            'out_of_stock' => 2, /* second */
            'soon' => 3, /* third */
            'draft' => 4, /* forth */
        ];

        if ($sortBy == 'newest') { /* newest sort is based on product.id */
            $products = $products->sortBy(function ($product) use ($statusSort) {
                $statusIndex = isset($statusSort[$product->status]) ? $statusSort[$product->status] : PHP_INT_MAX;
                return [$statusIndex, -$product->id]; /* with this negative (-) we can sort products based on id DESC */
            })->values();
        } else {
            $sortList = $this->getsortList($sortBy);
            $products = $products->sortBy(function ($product) use ($sortList, $statusSort) {
                $statusIndex = $statusSort[$product->status] ?? PHP_INT_MAX;
                // bellow line sort the products based on $sortList variable.
                $sortIndex = array_search($product->id, $sortList) ?? PHP_INT_MAX;
                return [$statusIndex, $sortIndex];
            })->values();
        }

        return $products;
    }
    public function getSortList($sortType) :array{
        return $this->cacheData['productsSortedList'][$sortType];
    }
    // sortByCategorySort & sortListsCreator method used just in cacheCreator method. this is not for use.
    private function sortByCategorySort(Category $category) :array
    {
        $sortByCategory = [];
        // now we should export product ids for this category
        $categorySort = CategoryProductSort::query()
            ->where('category_id', $category->id)
            ->orderBy('order')
            ->pluck('product_id')
            ->toArray();

        $sortByCategory = $categorySort;
        $children = $category->children()->active()->get();
        if ($children) {
            foreach ($children as $child) {
                $sortByCategory = array_merge($sortByCategory, $this->sortByCategorySort($child));
            }
        }
        return $sortByCategory;
    }
    private function sortListsCreator():array {
        $allProducts = $this->cacheData['productsCollection'];
        $allProductIds = $allProducts->pluck('id')->toArray();
        // sort byCategory =============================================================
        $sortByCategory = [];
        $categories = Category::query()->active()->orderBy('id','asc')->get();
        $mainCategories = $categories->whereNull('parent_id');
        foreach ($mainCategories as $mainCategory)
            $sortByCategory = array_merge($sortByCategory, $this->sortByCategorySort($mainCategory));
        // add other productIds at the end of sort list.
        $otherProductIds = array_diff($allProductIds, $sortByCategory);
        $sortByCategory = array_merge($sortByCategory,$otherProductIds);
        // =============================================================================
        // sort most_visited ===========================================================
        $allProducts->map(function ($product) { $product->setAppends(['views_count']); });
        $sortedProducts = $allProducts->sortByDesc('views_count')->values();
        $sortByMostVisited = $sortedProducts->pluck('id')->toArray();
        // =============================================================================
        // sort mostSales ==============================================================
        $sortByMostSell = OrderItem::query()
            ->select([DB::raw('SUM(quantity) as sum_quantity, product_id')])
            ->groupBy('product_id')
            ->orderby('sum_quantity', 'desc')
            ->pluck('product_id')->toArray();
        // =============================================================================
        // sort max_price ==============================================================
        $sortByMaxPrice = $allProducts->sortByDesc(function ($product){
            return $product->final_price['amount'];
        })->values()->pluck('id')->toArray();
        // =============================================================================
        // sort min_price ==============================================================
        $sortByMinPrice = array_reverse($sortByMaxPrice);
        // =============================================================================
        // sort mostDiscount ==========================================================
        $sortByMostDiscount = $allProducts->sortByDesc(function ($product){
            return $product->final_price['discount_price'];
        })->values()->pluck('id')->toArray();

        return [
            'byCategory' => $sortByCategory,
            'mostVisited' => $sortByMostVisited,
            'mostSales' => $sortByMostSell,
            'max_price' => $sortByMaxPrice,
            'min_price' => $sortByMinPrice,
            'mostDiscount' => $sortByMostDiscount,
        ];
    }
    // SORT SYSTEM =========================================================================
    // =====================================================================================
    // MINIMUM AND MAXIMUM PRICE OF PRODUCTS COLLECTION ====================================
    public static function getMinAndMaxPrice(Collection $products) :array {
        // ATTENTION: min and max of price depends on price of
        return [
            'max_price' => $products->max('final_price.amount'),
            'min_price' => $products->min('final_price.amount'),
        ];
    }

}
