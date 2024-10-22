<?php

namespace Modules\Product\Services;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Classes\CoreSettings;
use Modules\Product\Entities\Product;
/*
 | we sort products in search on 4 situation
 |
 | first: sameWithProductTitle
 | it means that the searchKeyWord exactly must exist in products.title with LIKE method on product.status=available
 |
 | second: sameWithProductTitleOutOfStock
 | it means that the searchKeyWord exactly must exist in products.title with LIKE method on product.status=out_of_stock
 |
 |
 | third: allWordsExistsInTitle
 | it means that all words of searchKeyWord must exist in product.title. on product.status=available
 | for example:
 |      searchKeyWord = روغن آرگان
 |      we will see products with title = روغن تراریخته آرگان
 | all words exists in title even there exists other words inside of them
 |
 |
 | forth: sameWithShortDescription
 | it means that the searchKeyWord exactly must exist in products.short_description with LIKE method on product.status=available
 |
 | fifth: eachWordsExistsInTitle
 | each words of searchKeyWord exist in product.title. on product.status=available
 | for example:
 |      searchKeyWord = روغن آرگان
 |      we will see products with title = روغن ارده
 |      we will see products with title = شانه آرگان
 | each words exists in title
 |
 |
 | SECOND:
 | after these 4 situations we sort on similar of category.title when result of last one was not enough
 |
 |
 |
 |
 |
 |
 * */
class ProductSearchService
{
    public Collection $allProducts;
    private $customizedSearchResult = [];
    private $searchKeyWord;
    private array $searchKeyWordArray = [];

    private $perPage;
    private bool $is_search;

    public function __construct($searchKeyWord, $is_search = true) {
        $this->searchKeyWord = $searchKeyWord;
        $this->searchKeyWordArray = explode(' ', $this->searchKeyWord);
        // is_search variable is because of that we don't calculate more than 10 ids on search mode.
        // and in search.index (occurs in front.products.index route) we should get all ids search to paginate it
        $this->is_search = $is_search;
        $this->perPage = app(CoreSettings::class)->get('product.searchPerPage');
        $this->allProducts = (new ProductsCollectionService())->getProductsCollection();
    }

    public function getProductIdsSearchResult() :array
    {
//        if (Cache::has($this->searchKeyWord)) return Cache::get($this->searchKeyWord);

        $coreSettingSearch = app(CoreSettings::class)->get('product.search');
        // exist searchKeyWord exactly on products.title on product.status = available =============================
        $newIds = $this->getSameWithProductTitle();
        if (!$this->addAndCheckToContinue($newIds)) return $this->customizedSearchResult;
        // exist searchKeyWord exactly on products.title on product.status = out_of_stock ==========================
        if ($coreSettingSearch['sameWithProductTitleOutOfStock']) {
            $newIds = $this->getSameWithProductTitleOutOfStock();
            if (!$this->addAndCheckToContinue($newIds)) return $this->customizedSearchResult;
        }
        // exist all words of searchKeyWord on products.title ======================================
        if ($coreSettingSearch['allWordsExistsInTitle']) {
            $newIds = $this->getAllWordsExistsInTitle();
            if (!$this->addAndCheckToContinue($newIds)) return $this->customizedSearchResult;
        }
        // exist searchKeyWord on products.description ======================================
        if ($coreSettingSearch['sameWithShortDescription']) {
            $newIds = $this->getSameWithShortDescription();
            if (!$this->addAndCheckToContinue($newIds)) return $this->customizedSearchResult;
        }
        // exist each words of searchKeyWord on products.title ======================================
        if ($coreSettingSearch['eachWordsExistsInTitle']) {
            $newIds = $this->getEachWordsExistsInTitle();
            if (!$this->addAndCheckToContinue($newIds)) return $this->customizedSearchResult;
        }

//        $searchKeyWordCacheTime = app(CoreSettings::class)->get('product.searchKeyWordCacheTime');
//        Cache::put($this->searchKeyWord, $this->customizedSearchResult, $searchKeyWordCacheTime);
        return $this->customizedSearchResult;
    }

    private function addAndCheckToContinue($newIds) :bool {
        $this->customizedSearchResult = array_values(array_unique(array_merge(
            $this->customizedSearchResult,
            $newIds
        )));
        if (!$this->is_search) return true;
        if (count($this->customizedSearchResult) < $this->perPage) return true;

        return false;
    }



    private function getSameWithProductTitle():array
    {
        $sameWithProductTitle = $this->allProducts
            ->where('status', Product::STATUS_AVAILABLE)
            ->filter(function ($product){
                // searching keyword on title and short_description. this is same as LIKE method in query. we are using of Eloquent\Collection. this is different
                if (str_contains($product->title, $this->searchKeyWord))
                    return $product;
                return false;
            })->values()->pluck('id')->toArray();

        return $sameWithProductTitle;
//        $this->customizedSearchResult = array_merge($this->customizedSearchResult, $sameWithProductTitle);

    }
    private function getSameWithProductTitleOutOfStock():array
    {
        $sameWithProductTitleOutOfStock = $this->allProducts
            ->where('status', Product::STATUS_OUT_OF_STOCK)
            ->filter(function ($product){
                // searching keyword on title and short_description. this is same as LIKE method in query. we are using of Eloquent\Collection. this is different
                if (str_contains($product->title, $this->searchKeyWord))
                    return $product;
                return false;
            })->values()->pluck('id')->toArray();
        return $sameWithProductTitleOutOfStock;
//        $this->customizedSearchResult = array_merge($this->customizedSearchResult, $sameWithProductTitleOutOfStock);

    }
    private function getAllWordsExistsInTitle():array
    {
        $allWordsExistsInTitle = [];
        if (count($this->searchKeyWordArray) > 1) {
            $isFirstFlag = true;
            foreach ($this->searchKeyWordArray as $searchKeyWordOneWord) {
                $singleProductIdsWithSearchKeyWordOneWord = $this->allProducts
                    ->where('status', Product::STATUS_AVAILABLE)
                    ->filter(function ($product) use ($searchKeyWordOneWord) {
                        // searching keyword on title and short_description. this is same as LIKE method in query. we are using of Eloquent\Collection. this is different
                        if (str_contains($product->title, $searchKeyWordOneWord))
                            return $product;
                        return false;
                    })->values()->pluck('id')->toArray();

                if ($isFirstFlag) {
                    $allWordsExistsInTitle = array_merge($allWordsExistsInTitle, $singleProductIdsWithSearchKeyWordOneWord);
                    $isFirstFlag = false;
                }
                else // get subscription of two arrays. Ids must be common with all results of this foreach method
                    $allWordsExistsInTitle = array_values(array_intersect($allWordsExistsInTitle,$singleProductIdsWithSearchKeyWordOneWord));
            }
        }
        return $allWordsExistsInTitle;
    }
    private function getSameWithShortDescription():array
    {
        $sameWithShortDescription = $this->allProducts
            ->where('status', Product::STATUS_AVAILABLE)
            ->filter(function ($product) {
                // searching keyword on title and short_description. this is same as LIKE method in query. we are using of Eloquent\Collection. this is different
                if (str_contains($product->short_description, $this->searchKeyWord))
                    return $product;
                return false;
            })->values()->pluck('id')->toArray();
        return $sameWithShortDescription;
    }
    private function getEachWordsExistsInTitle():array
    {
        $eachWordsExistsInTitle = [];
        if (count($this->searchKeyWordArray) > 1) {
            foreach ($this->searchKeyWordArray as $searchKeyWordOneWord) {
                $singleProductIdsWithSearchKeyWordOneWord = $this->allProducts
                    ->where('status', Product::STATUS_AVAILABLE)
                    ->filter(function ($product) use ($searchKeyWordOneWord) {
                        // searching keyword on title and short_description. this is same as LIKE method in query. we are using of Eloquent\Collection. this is different
                        if (str_contains($product->title, $searchKeyWordOneWord))
                            return $product;
                        return false;
                    })->values()->pluck('id')->toArray();
                $eachWordsExistsInTitle = array_merge($eachWordsExistsInTitle, $singleProductIdsWithSearchKeyWordOneWord);
            }
        }
        return $eachWordsExistsInTitle;
    }
}
