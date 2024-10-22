<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Product\Entities\CustomRelatedProduct;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\Admin\CustomRelatedProductRequest;

class CustomRelatedProductController extends Controller
{
    public function index($id)
    {
        $customRelatedProducts = CustomRelatedProduct::query()
            ->where('product_id',$id)
            ->get();
        $products = Product::select('id','title')->get();

        return view('product::admin.custom-related-product.index',compact('customRelatedProducts','products'));

//        return response()->success('',compact('customRelatedProducts'));
    }

    public function store(CustomRelatedProductRequest $request)
    {
        $searchInDb= CustomRelatedProduct::query()
            ->where('product_id',$request->product_id)
            ->where('related_id',$request->related_id)
            ->count();


        if ($searchInDb !=0){
            return response()->error('نمیتوانید تکراری انتخاب کنید');
        }
        $customRelatedProduct = CustomRelatedProduct::create($request->validated());
        return redirect()->back()->with('success','عملیات با موفقیت انجام شد');
    }

    public function destroy($id)
    {
        $customRelatedProduct = CustomRelatedProduct::findOrFail($id);
        $customRelatedProduct->delete();

        return redirect()->route('admin.custom-related-product.index',$customRelatedProduct->product_id)
            ->with('success','عملیات با موفقیت انجام شد');

//        return response()->success('',compact('customRelatedProduct'));
    }
}
