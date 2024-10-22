<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Http\Requests\Admin\OrderProductStoreRequest;

class OrderProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->where('order','!=',null)->orderBy('order')->select('id','title')->get();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('',compact('products'));
        }
        return view('product::admin.sort.index',compact('products'));
    }


    public function store(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        Product::sortOrders($product, Product::getMaxOrder());

        if (request()->header('Accept') == 'application/json') {
          return response()->success('با موفقیت ثبت شد',compact('product'));
        }
        return redirect()->route('admin.order-product.index')
        ->with('success', 'محصول با موفقیت ثبت شد.');
    }


    public function changeOrder(Request $request)
    {
        $order = 1;
        $ids = $request->ids;
        $products = Product::query()
            ->whereNotNull('order')
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(`id`, ' . implode(", ", $ids) . ')')
            ->get();


        foreach ($products as $id => $product) {
            $product->update(['order' => $id]);
        }

        if (request()->header('Accept') == 'application/json') {
          return $products->get();
        }
        return redirect()->route('admin.order-product.index')
        ->with('success', 'محصولات مرتب سازی شد.');
    }

    public function makeOrderIdNull($id)
    {
        $product = Product::find($id);
        Product::sortOrders($product, 100000000000);
        $product->update([
            'order' => null
        ]);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('ok');
        }
        return redirect()->route('admin.order-product.index')
        ->with('success', 'محصول با موفقیت حذف شد.');
    }
}
