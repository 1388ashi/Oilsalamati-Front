<?php

namespace Modules\Home\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Core\Classes\CoreSettings;
use Modules\Home\Entities\SiteView;
use Modules\Home\Services\BaseService;
use Modules\Home\Services\HomeService;
use Modules\Advertise\Entities\Advertise;
use Modules\Blog\Entities\Post;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Slider\Entities\Slider;

class HomeController extends Controller
{

  private $productSelectedColumns = Product::SELECTED_COLUMNS_FOR_FRONT;
  private $productAppends = Product::APPENDS_LIST_FOR_FRONT;

  public function index()
  {
    SiteView::store();

    // $homeService = new HomeService();
    // $response = $homeService->getHomeData();

    $response = [
      'mostSales' => $this->mostSales(),
      'mostDiscount' => $this->mostDiscount(),
      'post' => $this->post(),
      'sliders' => $this->sliders(),
      'advertise' => $this->advertise(),
      'categories' => $this->categories(),
      'specialCategories' => $this->specialCategories(),
      'mostVisited' => $this->mostVisited(),
      'newProducts' => $this->newProducts(),
      'isPackage' => $this->isPackage(),
    ];

    return view('home::home', compact('response'));
  }

  private function isPackage()
  {
    $products = Product::query()
      ->select($this->productSelectedColumns)
      ->where('is_package', 1)
      ->limit(4)
      ->get();

    foreach ($products as $product) {
      $product->setAppends($this->productAppends);
    }
    return $products;
  }

  private function mostVisited()
  {
    $sortListByMostSales = (new ProductsCollectionService())->getSortList('mostVisited');
    $productIds = array_slice($sortListByMostSales, 0, 8);
    $products = Product::query()->select($this->productSelectedColumns)->whereIn('id', $productIds)->get();
    foreach ($products as $product) {
      $product->setAppends($this->productAppends);
    }
    return $products;
  }

  private function newProducts()
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

  private function categories()
  {
    return Category::query()
      ->with('children')
      ->active()
      ->orderBy('priority', 'DESC')
      ->parents()
      ->get();
  }

  public function specialCategories()
  {
    return Category::query()
      ->select(['id', 'title', 'slug', 'en_title'])
      ->special()
      ->active()
      ->withCount('products')
      ->latest('id')
      ->take(10)
      ->get();
  }

  private function mostSales()
  {
    $sortListByMostSales = (new ProductsCollectionService())->getSortList('mostSales');
    $productIds = array_slice($sortListByMostSales, 0, 10);
    $products = Product::query()->select($this->productSelectedColumns)->whereIn('id', $productIds)->get();
    foreach ($products as $product) {
      $product->setAppends($this->productAppends);
    }
    return $products;
  }

  private function mostDiscount()
  {
    $sortListByMostSales = (new ProductsCollectionService())->getSortList('mostDiscount');
    $productIds = array_slice($sortListByMostSales, 0, 10);
    $products = Product::query()->select($this->productSelectedColumns)->whereIn('id', $productIds)->get();
    foreach ($products as $product) {
      $product->setAppends($this->productAppends);
    }
    return $products;
  }

  private function post()
  {
    return Post::query()
      ->select(['id', 'title', 'summary', 'published_at'])
      ->where('is_magazine', 0)
      ->published()
      ->latest('id')
      ->take(10)
      ->get();
  }

  private function sliders()
  {
    return Slider::query()
      ->latest('order')
      ->active()
      ->where('group', 'header')
      ->take(10)
      ->get();
  }

  private function advertise()
  {
    return Advertise::getForHome();
  }


  public function base(): JsonResponse
  {
    $baseItems = app(CoreSettings::class)->get('home.base');
    $baseRouteService = new BaseService();
    $response = $baseRouteService->getBaseRouteCacheData();


    return response()->success(':)', compact('response'));
  }

  public function get_user(): JsonResponse
  {
    $baseRouteService = new BaseService();
    $user = $baseRouteService->getUser();
    (new \Modules\CustomersClub\Http\Controllers\Admin\CustomersClubController)->setDailyLoginScore();
    return response()->success('کاربر', compact('user'));
  }


  public function item($itemName)
  {
    $coreSetting = app(CoreSettings::class);
    $homeItems = $coreSetting->get('home.front');

    if (!key_exists($itemName, $homeItems))
      return response()->error('آیتم در صفحه اصلی موجود نیست');

    $homeService = new HomeService();
    $response = $homeService->getHomeDataItem($itemName);
    return response()->success('', compact('response'));
  }
}
