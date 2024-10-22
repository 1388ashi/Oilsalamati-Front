<?php

namespace Modules\Category\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;

//use Shetabit\Shopit\Modules\Category\Http\Controllers\Front\CategoryController as BaseCategoryController;

class CategoryController extends Controller
{
    public function special()
    {
        $categories = Category::query()->active()->with('children')
            ->where('show_in_home' , true)
            ->orderBy('priority', 'DESC')
            ->filters()
            ->get();

        return  response()->success('تمام دسته بندی ها', compact('categories'));
    }

    // came from vendor ================================================================================================

    public function index()
    {
        $categories = Category::query()->active()->parents()->with('children')
            ->orderBy('priority', 'DESC')
            ->filters()
            ->paginate(30);

        return  response()->success('تمام دسته بندی ها', compact('categories'));
    }

    public function show($id)
    {
        $category = Category::query()->with([
            'products' => function ($query) { return $query->select(Product::SELECTED_COLUMNS_FOR_FRONT); }
        ])->findOrFail($id);

        return response()->success('', compact('category'));
    }


}
