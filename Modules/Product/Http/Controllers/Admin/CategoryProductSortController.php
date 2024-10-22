<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Helper;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Entities\CategoryProductSort;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\Admin\CategoryProductrtUpdate;
use Modules\Product\Http\Requests\Admin\CategoryProductSortStore;
use PhpOffice\PhpSpreadsheet\Calculation\LookupRef\Sort;

class CategoryProductSortController extends Controller
{

    #TODO (S):
    # 1- sort by this list in front

    public function index($id)
    {
        $products = Product::select('id','title')->get();
        $sorts = CategoryProductSort::query()
            ->where('category_id',$id)
            ->orderBy('order')
            ->paginate(100);
        foreach ($sorts as $sort){
            $sort->product_title = $sort->product->title;
            $sort->makeHidden('product');
        }
        if (request()->header('Accept') == 'application/json') {
            return response()->success('',compact('sorts'));
		}
        return view('category::admin.show', compact('sorts','products','id'));
    }

    public function store(CategoryProductSortStore $request)
    {
        $exists = CategoryProductSort::query()
            ->where('category_id',$request->category_id)
            ->where('product_id',$request->product_id)
            ->count();
        if ($exists){
            return response()->error('مورد انتخاب شده تکراری است...');
        }

        $sort = CategoryProductSort::create($request->validated());
        $product = Product::find($request->product_id);
        $sort->product_title = $product->title;


        //بیاد اول توی همون دسته بندی
        CategoryProductSort::sortOrders($sort,1,$request->category_id);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('',compact('sort',));
        }
        return redirect()->back()->with('success', 'محصول با موفقیت ثبت شد.');
    }


    public function show($id)
    {
        $sort = CategoryProductSort::findOrFail($id);

        return response()->success('',compact('sort'));
    }

    #update only order
    public function update(CategoryProductrtUpdate $request, $id)
    {
        $orderedServices = $request->input('orders');
        $categoryId = $request->input('category_id');

        $services = CategoryProductSort::query()->where('category_id', $categoryId)->get(['id', 'order']);

        $orderMap = [];

        foreach ($orderedServices as $order => $serviceId) {
            $orderMap[$serviceId] = $order;
        }
        foreach ($services as $service) {
            if (isset($orderMap[$service->id])) {
                $service->order = $orderMap[$service->id];
                $service->save();
            }
        }
        if (request()->header('Accept') == 'application/json') {
            return response()->success('',compact('sort'));
        }
        return redirect()->back()->with('success', 'محصولات دسته بندی با موفقیت مرتب سازی شد.');
//         $sort = CategoryProductSort::findOrFail($id);
//         $sort->update($request->validated());
//         CategoryProductSort::sortOrders($sort, $request->input('order'),$sort->category_id);

//         return response()->success('',compact('sort'));
    }

    public function destroy($id)
    {
        $sort = CategoryProductSort::findOrFail($id);
        $sort->delete();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('',compact('sort'));
        }
        return redirect()->back()->with('success', 'محصول با موفقیت حذف شد.');
    }
}
