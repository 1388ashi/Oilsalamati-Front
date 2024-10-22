<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Product\Entities\Recommendation;
use Modules\Product\Entities\RecommendationItem;
use Modules\Product\Http\Requests\Admin\RecommendationItemStoreRequest;
use Modules\Product\Services\ProductsCollectionService;

class RecommendationItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Recommendation $recommendation)
    {
        $recommendationItems = RecommendationItem::where('recommendation_id', $recommendation->id)->with('product:id,title')->orderBy('priority', 'asc')->get();
        $products = (new ProductsCollectionService())->getProductsCollection();

        return view('product::admin.recommendation-items.index', compact('products', 'recommendationItems', 'recommendation'));
    }

    public function store(RecommendationItemStoreRequest $request)
    {
        $data = [];
        foreach ($request->products as $item) {
            array_push($data, [
                'product_id' => $item,
                'recommendation_id' => $request->recommendation_id,
            ]);
        }
        $recommendationItem = RecommendationItem::insert($data);

        return redirect()->back()->with('success', 'آیتم گروه با موفقیت ثبت شد.');
    }
    public function sort(Request $request)
    {
        RecommendationItem::setNewOrder($request->recommendation_items);

        return redirect()->back()->with('success', 'گروه محصولات با موفقیت مرتب شد.');
    }
    // public function update(RecommendationItemStoreRequest $request, Recommendation $recommendation, RecommendationItem $recommendationItem)
    // {
    //     $recommendationItem->update($request->validated());
    //     ActivityLogHelper::updatedModel('آیتم گروه محصول ویرایش شد', $recommendationItem);

    //     return redirect()->back()->with('success', 'آیتم گروه با موفقیت به روزرسانی شد.');
    // }
    public function destroy(Recommendation $recommendation, RecommendationItem $recommendationItem)
    {
        $recommendationItem->delete();

        return redirect()->back()->with('success', 'آیتم گروه با موفقیت حذف شد.');
    }
}
