<?php

namespace Modules\Category\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;
use Modules\Core\Services\Cache\CacheServiceInterface;

class CategoriesCollectionService extends CacheServiceInterface
{
    public static array $usedModelsInCache = [Category::class];

    protected function constructNeedId(): bool { return false; }

    public function cacheCreator($model_id): void
    {
        $categories = Category::query()->select(['id','title','slug','parent_id','priority'])->get();
        $this->cacheData['categoriesCollection'] = $categories;


        foreach ($categories as $category) {
            $this->cacheData['productsOfCategory'][$category->id]['directProducts'] = $this->getProductsOfCategory($category);
            $this->cacheData['productsOfCategory'][$category->id]['childrenProducts'] = $this->getProductsOfChildrenOfCategory($category);
        }

        Cache::forever(self::getCacheName(), $this->cacheData);
    }

    private function getProductsOfCategory(Category $category):array
    {
        return $category->products()->get()->pluck('id')->toArray();
    }
    private function getProductsOfChildrenOfCategory(Category $category):array
    {
        $productsId = [];
        foreach ($category->children ?? [] as $category){
            $productsId = array_merge($productsId, $this->getProductsOfCategory($category));
        }
        return $productsId;
    }
    // =================================================================================
    // =================================================================================
    public function getAllProductsOfCategory($category_id):array {
        return array_values(array_unique(array_merge(
            $this->cacheData['productsOfCategory'][$category_id]['directProducts'],
            $this->cacheData['productsOfCategory'][$category_id]['childrenProducts']
        )));
    }
    public function getDirectProductsOfCategory($category_id):array {
        return $this->cacheData['productsOfCategory'][$category_id]['directProducts'];
    }
    public function getChildrenProductsOfCategory($category_id):array {
        return $this->cacheData['productsOfCategory'][$category_id]['childrenProducts'];
    }
}
