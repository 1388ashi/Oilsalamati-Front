<?php

namespace Modules\Home\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Advertise\Entities\Advertise;
use Modules\Blog\Entities\Post;
use Modules\Cart\Classes\CartFromRequest;
use Modules\Category\Entities\Category;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Entities\Media;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Customer\Entities\ValidCustomer;
use Modules\Home\Entities\BeforeAfterImage;
use Modules\Instagram\Entities\Instagram;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Recommendation;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Slider\Entities\Slider;

class HomeService extends CacheServiceInterface
{
    private $productSelectedColumns = Product::SELECTED_COLUMNS_FOR_FRONT;
    private $productAppends = Product::APPENDS_LIST_FOR_FRONT;

    protected function constructNeedId(): bool { return false; }
    public function cacheCreator($model_id = null) :void {

        $coreSetting = app(CoreSettings::class);
        $homeItems = $coreSetting->get('home.front');

        foreach ($homeItems as $homeItem => $inputs) {
            if ($inputs['enabled']) {
                $this->cacheData[$homeItem] = $this->{$homeItem}($inputs);
            }
        }
        
        Cache::put(self::getCacheName(), $this->cacheData, $coreSetting->get('home.front_cache_time'));
    }


    public function getHomeData() {
        return $this->cacheData;
    }
    public function getHomeDataItem($item) {
        return $this->cacheData[$item];
    }

    private function post($inputs)
    {
        return Post::query()
            ->select(['id', 'title', 'summary', 'published_at'])
            ->where('is_magazine', 0)
            ->published()
            ->latest($inputs['latestBy'])
            ->take($inputs['take'])
            ->get();
    }

    private function magazine($inputs)
    {
        return Post::query()->with('category')
            ->orderBy('pin', 'desc')
            ->where('is_magazine', 1)
//            ->withCount('views')
            ->published()
            ->latest($inputs['latestBy'])->take($inputs['take'])
            ->get()
            ->map(function ($item) {
                $item->makeHidden(['body']);
                $item->setAppends(['views_count']);
                return $item;
//                return $item->makeHidden(['body']);
            });
    }

    private function sliders($inputs)
    {
        return Slider::query()
            ->latest('order')
            ->active()
            ->where('group', 'header')
            ->take($inputs['take'])
            ->get();
    }

    private function advertise($inputs)
    {
        return Advertise::getForHome();
    }

    private function instagram() {
        return Instagram::getInstagramPosts();
    }

    private function suggestions($inputs) {
        $recommendation_suggestion = Recommendation::query()->where('group_name','suggestions')->first();

        if (!$recommendation_suggestion || !$recommendation_suggestion->status) return [];

        $suggestions = $recommendation_suggestion->recommendationItems()
            ->orderBy('priority', 'asc')
            ->limit($inputs['take'])
            ->get();

        foreach ($suggestions as $suggestion) {
            $product = $suggestion->product()->select($this->productSelectedColumns)->first();
            $product->setAppends($this->productAppends);
            $suggestion->product = $product;
        }
        return $suggestions;
    }

    private function newProducts($inputs)
    {
        $products = Product::where('new_product_in_home', 1)
            ->select($this->productSelectedColumns)
            ->orderBy('id', 'DESC')
            ->get();
        foreach ($products as $product) {
            $product->setAppends($this->productAppends);
        }
        return $products;
    }

    private function freeShippingProducts($inputs)
    {
        $free_shipping_products = Product::query()
            ->select($this->productSelectedColumns)
            ->where('free_shipping', '=', true)
            ->available(true)
            ->orderBy('updated_at', 'desc')
            ->limit($inputs['take'])
            ->get();
        foreach ($free_shipping_products as $product) {
            $product->setAppends($this->productAppends);
        }

        return $free_shipping_products;
    }

    private function homeBoxCreator($databaseHomeBox)
    {
        $homeBoxItems = $databaseHomeBox->recommendationItems()->orderBy('priority', 'asc')->get();
        $items = [];
        foreach ($homeBoxItems as $homeBoxItem) {
            $product = $homeBoxItem->product()->select($this->productSelectedColumns)->first();
            $product->setAppends($this->productAppends);
            $items[] = $product;
        }
        $databaseHomeBox->items = $items;
        return $databaseHomeBox;
    }

    private function homeBoxes($inputs)
    {
        $databaseHomeBoxes = Recommendation::query()->where('group_name','home')->active()->orderBy('priority', 'asc')->get();
        if (!$databaseHomeBoxes) return [];

        $homeBoxes = [];
        for ($i = 0; $i < $databaseHomeBoxes->count(); $i++) {
            if ($i>=4) {
                $homeBoxes['extra'][] = $this->homeBoxCreator($databaseHomeBoxes[$i]);
            } else {
                $name = '';
                switch ($i+1) {
                    case 1: $name = 'one'; break;
                    case 2: $name = 'two'; break;
                    case 3: $name = 'three'; break;
                    case 4: $name = 'four'; break;
                }
                $homeBoxes[$name] = $this->homeBoxCreator($databaseHomeBoxes[$i]);
            }
        }
        return $homeBoxes;
    }

    private function mostSales($inputs)
    {
        $sortListByMostSales = (new ProductsCollectionService())->getSortList('mostSales');
        $productIds = array_slice($sortListByMostSales, 0, $inputs['take']);
        $products = Product::query()->select($this->productSelectedColumns)->whereIn('id', $productIds)->get();
        foreach ($products as $product) {
            $product->setAppends($this->productAppends);
        }
        return $products;
    }

    private function mostDiscount($inputs)
    {
        $sortListByMostSales = (new ProductsCollectionService())->getSortList('mostDiscount');
        $productIds = array_slice($sortListByMostSales, 0, $inputs['take']);
        $products = Product::query()->select($this->productSelectedColumns)->whereIn('id', $productIds)->get();
        foreach ($products as $product) {
            $product->setAppends($this->productAppends);
        }
        return $products;
    }

    private function mostVisited($inputs)
    {
        $sortListByMostSales = (new ProductsCollectionService())->getSortList('mostVisited');
        $productIds = array_slice($sortListByMostSales, 0, $inputs['take']);
        $products = Product::query()->select($this->productSelectedColumns)->whereIn('id', $productIds)->get();
        foreach ($products as $product) {
            $product->setAppends($this->productAppends);
        }
        return $products;
    }

    private function beniBox($inputs)
    {
        $products = Product::query()
            ->select(array_merge($this->productSelectedColumns, ['discount_until']))
            ->where('discount', '>', 0)
            ->where('is_benibox', 1)
            ->where('discount_until', '>', date("Y-m-d H:i:s"))
            ->whereRaw('discount_until < DATE_ADD(NOW(), INTERVAL 7 DAY)')
            ->get();

        foreach ($products as $product) {
            $product->setAppends($this->productAppends);
        }
        return $products;
    }

    private function isPackage($inputs)
    {
        $products = Product::query()
            ->select($this->productSelectedColumns)
            ->where('is_package', 1)
            ->limit($inputs['take'])
            ->get();

        foreach ($products as $product) {
            $product->setAppends($this->productAppends);
        }
        return $products;
    }

    public function beforeAfterImages($inputs)
    {
        $row = BeforeAfterImage::where('type','before')->where('enabled',1)->orderBy('id','desc')->get();

        $list = array();
        foreach ($row as $item) {
            $before_id = $item->id;
            $after = BeforeAfterImage::where('uuid',$item->uuid)->where('type','after')->first();
            $after_id = $after->id;

            /* @var $before_image Media */
            $before_image = Media::query()
                ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                ->where('model_id',$before_id)
                ->where('collection_name','before_after_image_before')
                ->first();
            $before_image = MediaDisplay::objectCreator($before_image);
            /* @var $after_image Media */
            $after_image = Media::query()
                ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                ->where('model_id',$after_id)
                ->where('collection_name','before_after_image_after')
                ->first();
            $after_image = MediaDisplay::objectCreator($after_image);
            /* @var $customer_image Media */
            $customer_image = Media::query()
                ->where('model_type','Modules\Home\Entities\BeforeAfterImage')
                ->where('model_id',$before_id)
                ->where('collection_name','before_after_image_customer')
                ->first();
            $customer_image = MediaDisplay::objectCreator($customer_image);

            $product = (new ProductsCollectionService())->getProductObjectFromProductId($item->product_id);

            $list[] = [
                'id' => $item->id,
                'customer_name' => $item->customer_name,
                'title_before' => $item->title,
                'title_after' => $after->title,
                'short_description' => $item->short_description,
                'full_description' => $item->full_description,
                'enabled' => (bool)$item->enabled,
                'product' => $product,
                'before_image' => $before_image,
                'after_image' => $after_image,
                'customer_image' => $customer_image,
            ];
        }
        return $list;
    }

    public function validCustomers($inputs) {
        return ValidCustomer::query()
            ->latest('id')
            ->active()
            ->get();
    }

    private function categories($inputs) 
    {
        return Category::query()
            ->with('children')
            ->active()
            ->orderBy('priority', 'DESC')
            ->parents()
            ->get();
    }

    public function specialCategories($inputs)
    {
        return Category::query()
            ->take($inputs['take'])
            ->special()
            ->active()
            ->latest('id')
            ->get();
    }



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function cart_request()
    {
        return CartFromRequest::checkCart($this->request);
    }

    // دادن کارت های مجازی(کوکی)
    public function getCartFromRequest()
    {
        return CartFromRequest::checkCart($this->request);
    }

}
