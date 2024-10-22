<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Attribute\Entities\Attribute;
use Modules\Category\Entities\Category;
use Modules\Category\Http\Requests\Admin\CategorySortRequest;
use Modules\Category\Http\Requests\Admin\CategoryStoreRequest;
use Modules\Category\Http\Requests\Admin\CategoryUpdateRequest;
use Modules\Specification\Entities\Specification;

//use Shetabit\Shopit\Modules\Category\Http\Controllers\Admin\CategoryController as BaseCategoryController;

class CategoryController extends Controller
{
    private function prepareDataForTree($categories) {
        $tree = [];
        foreach ($categories as $category) {
            $item = [
                '_id' => $category->id,
                'parent_id' => $category->parent_id,
                'title' => $category->title,
                'level' => $category->level ,
            ];
            $tree[] = $item;
        }
        return $tree;
    }
    public function index($id = null)
    {
        // $categories = Category::query()
        //     ->parents()
        //     ->orderByDesc('priority')
        //     ->filters();

        // if (\request('all')) {
        //     $categories->with('children');
        // }
        // $categories = $categories->get();
        $categories = Category::where('parent_id',$id)->orderByDesc('priority')->paginate();
        // $categoriesTreeData = $this->prepareDataForTree($categories);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('تمام دسته بندی ها', compact('categories'));
		}

        return view('category::admin.index', compact('categories'));
    }



    // came from vendor ================================================================================================
    public function indexLittle()
    {
        $categories = Category::without('media')->parents()->orderBy('priority', 'DESC')->filters();
        if (\request('all')) {
            $categories->with('children');
        }
        $categories = $categories->paginateOrAll();

        return  response()->success('تمام دسته بندی ها', compact('categories'));
    }

    public function create()
    {
        $categories = Category::select('id', 'title')->get();
        $attributes = Attribute::select('id', 'label')->get();
        $specifications = Specification::select('id', 'label')->get();

        return view('category::admin.create', compact(['categories', 'attributes', 'specifications']));
    }

    public function store(CategoryStoreRequest $request , Category $category)
    {
        $category->fill($request->all());

        if($request->parent_id != null){
            $findParent = Category::query()->find($request->parent_id);
            $category->level = $findParent->level + 1 ;
        }
        $category->save();
        $category->attributes()->attach($request->attribute_ids);
        $category->specifications()->attach($request->specification_ids);
        $category->brands()->attach($request->brand_ids);
        ActivityLogHelper::storeModel('دسته بندی ثبت شد', $category);


        if ($request->hasFile('image')) {
            $category->addImage($request->file('image'));
        }
        if ($request->hasFile('icon')) {
            $category->addIcon($request->file('icon'));
        }

        if (request()->header('Accept') == 'application/json') {
            return response()->success('دسته بندی با موفقیت ایجاد شد.', compact('category'));
		}

        return redirect()->route('admin.categories.index')->with('success', 'دسته بندی با موفقیت ایجاد شد');

    }

    public function sort(CategorySortRequest $request)
    {
        Category::sort($request->input('categories'));
        Cache::deleteMultiple(['home_special_category', 'home_category']);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('مرتب سازی با موفقیت انجام شد');
		}
        return redirect()->back()->with('success', 'مرتب سازی با موفقیت انجام شد');

    }

    public function show($id)
    {
        $category = Category::query()->find($id);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('', compact('category'));
		}
        return view('category::admin.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $categories = Category::select('id', 'title')->get();
        $attributes = Attribute::select('id', 'label')->get();
        $specifications = Specification::select('id', 'label')->get();

        return view('category::admin.edit', compact(['categories', 'attributes', 'specifications', 'category']));
    }

    public function update(CategoryUpdateRequest $request, $id)
    {
        $category = Category::query()->find($id);
        $category->fill($request->validated());
        if ($request->hasFile('image')) {
            $category->addImage($request->image);
        }
        if ($request->hasFile('icon')) {
            $category->addIcon($request->icon);
        }

        $category->attributes()->sync($request->attribute_ids);
        $category->specifications()->sync($request->specification_ids);
        $category->brands()->sync($request->brand_ids);

        $category->save();
        ActivityLogHelper::updatedModel('دسته بندی بروز شد', $category);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('دسته بندی با موفقیت بروزرسانی شد', compact('category'));
		}

        return redirect()->route('admin.categories.index')->with('success', 'دسته بندی با موفقیت بروزرسانی شد');
    }


    public function destroy($id)
    {
        $category = Category::query()->findOrFail($id);
        $category->delete();
        ActivityLogHelper::deletedModel('دسته بندی حذف شد', $category);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('دسته بندی با موفقیت حذف شد', compact('category'));
		}

        return redirect()->route('admin.categories.index')->with('success', 'دسته بندی با موفقیت حذف شد');
    }
}
