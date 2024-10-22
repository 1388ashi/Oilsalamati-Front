<?php

namespace Modules\Product\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Modules\Category\Services\CategoriesCollectionService;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
//use Modules\Core\Http\Controllers\BaseRouteController;
use Modules\Product\Entities\Product;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Product\Services\ProductSearchService;
use Modules\ProductQuestion\Entities\ProductQuestion;

//use Modules\Product\Services\ProductService;
//use phpDocumentor\Reflection\Types\True_;
//use Shetabit\Shopit\Modules\Product\Http\Controllers\Front\ProductController as BaseProductController;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $is_search = false;
        if (request()->has('searchKeyWord') && request()->searchKeyWord) $is_search = true;

        $cacheProductCollection = (new ProductsCollectionService())->getProductsCollection();
        $products = $cacheProductCollection;

        // ======================================================================================================
        // FILTER SECTION =======================================================================================
        if ($is_search) {
            // this is search mode. because searchKeyWord exists in request
            $productIdsSearchResult = (new ProductSearchService(request()->searchKeyWord, is_search: false))->getProductIdsSearchResult();
            $products = $products->whereIn('id', $productIdsSearchResult)->values();
        } else {
            // this is not search mode. this is product.index
            if (request()->has('category_id') && request('category_id')) {
                // here exists category_id in request. so we should return just products of this category's products
                $categoryProductIds = (new CategoriesCollectionService())->getAllProductsOfCategory(request('category_id'));
                $products = $products->whereIn('id', $categoryProductIds)->values();
            }
        }

        // filter on min and max price. if exists.
        if (request('min_price') && request('max_price') && request('min_price') <= request('max_price')) {
            $products = $products->filter(function ($product) {
                if ($product->final_price['amount'] >= request('min_price') && $product->final_price['amount'] <= request('max_price'))
                    return $product;
                return false;
            })->values();
        }
        // just available ================
        if (request()->has('justAvailable') && request('justAvailable')) {
            $products = $products->filter(function ($product) {
                return ($product->status == Product::STATUS_AVAILABLE) ? $product : false;
            });
        }

        // ======================================================================================================
        // SORT SECTION =========================================================================================
        if ($is_search) {
            $products = $products->sortBy(function ($product) use ($productIdsSearchResult) {
                return array_search($product->id, $productIdsSearchResult) !== false ? array_search($product->id, $productIdsSearchResult) : PHP_INT_MAX;
            })->values();
        } else {
            $products = (new ProductsCollectionService())->sortSystem($products);
        }

        // find min & max prices for filter.
        // ATTENTION: priceFilter must be before of pagination. because pagination slices the products collection, while max&min prices must be on all of products
        $priceFilter = ProductsCollectionService::getMinAndMaxPrice($products);
        // pagination ======================================
        $currentPage = request()->get('page', 1);
        $pagination = app(CoreSettings::class)->get('product.pagination');
        $pageCount = intdiv($products->count(), $pagination) + 1;
        $allProductsCount = $products->count();
        $products = $products->slice(($currentPage - 1) * $pagination, $pagination)->values();


        foreach ($products as $product) {
            $product->setAppends(['final_price','images_showcase']);
            $product->makeHidden('short_description');
        }


        return response()->success('لیست تمامی محصولات',
            compact('products', 'priceFilter', 'pageCount','allProductsCount'));
    }
    public function show($id): JsonResponse
    {
        /* todo: fix it. load product from service. and also add questions. we should send categories and brands to the ProductDetailsService */
        /** @var $product Product */
        $product = Product::query()->with(['productComments', 'categories', 'brand'])->active()/*->with('varieties')*//*->with('varieties.attributes.pivot', 'varieties.color')*//*->withCommonRelations()*/->findOrFail($id);

        $product->setAppends(['final_price','specifications_showcase','sizeCharts_showcase','images_showcase']);
        $product->loadVarietiesShowcase($product->varieties);

        foreach ($product->varieties as $variety) {
            $variety->setAppends(['final_price','images_showcase']);
        }

//        $product->load_gifts();

        $relatedProducts = collect();
        $relatedProducts1 = Helpers::getRelatedProducts($product,true);
//        $relatedProducts1 = collect();

        $productQuestions = ProductQuestion::query()
            ->where('product_id' , $product->id)
            ->status(ProductQuestion::STATUS_APPROVED)
            ->MainQuestion()
            ->with(['answers' => function ($query) {
                $query->where('status', 'approved');
            }])
//            ->filters()
            ->get();

        return response()->success('', compact('product', 'relatedProducts','relatedProducts1','productQuestions'));
    }

    public function search()
    {
        $searchKeyWord = request('q');
        if (!$searchKeyWord || mb_strlen($searchKeyWord) === 1) {
            return null;
        }
//        $coreSetting = app(CoreSettings::class);
//
//        $numberPattern = $coreSetting->get('search.products.number_pattern');
//        if (is_numeric($searchKeyWord) && $numberPattern)
//            $searchKeyWord = str_replace('{number}', $searchKeyWord, $numberPattern);



        $productIdsSearchResult = (new ProductSearchService($searchKeyWord, is_search: true))->getProductIdsSearchResult();
        /* @var $cacheProductCollection Collection */
        $cacheProductCollection = (new ProductsCollectionService())->getProductsCollection();
        $products = $cacheProductCollection
            ->whereIn('id', $productIdsSearchResult)
            ->sortBy(function ($product) use ($productIdsSearchResult) {
                return array_search($product->id, $productIdsSearchResult) !== false ? array_search($product->id, $productIdsSearchResult) : PHP_INT_MAX;
            })
            ->take(app(CoreSettings::class)->get('product.searchPerPage'))
            ->values();

//        if ($c = request('c')) {
//            $products->whereHas('category', function ($query) use ($c) {
//                $query->where('id', $c);
//            });
//        }

        foreach ($products as $product) {
            $product->setAppends(['final_price','images_showcase']);
        }
        return response()->success('', compact('products'));
    }

}
