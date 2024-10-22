<?php

namespace Modules\Product\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Controllers\Admin\RecommendationController as BaseRecommendationController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Link\Http\Controllers\Admin\LinkController;
use Modules\Product\Entities\Recommendation;
use Modules\Product\Http\Requests\Admin\RecommendationSortRequest;
use Modules\Product\Http\Requests\Admin\RecommendationStoreRequest;
use Modules\Product\Http\Requests\Admin\RecommendationUpdateRequest;

class RecommendationController extends Controller
{
    public function index()
    {
        $linkableData = (new LinkController)->create();

        $dataArray = $linkableData->getData(true);

        if ($dataArray['success'] && isset($dataArray['data']['linkables'])) {
            $linkables = $dataArray['data']['linkables'];
        }
        $recommendations = Recommendation::orderBy('group_name', 'asc')->orderBy('priority', 'asc')->get();
        $existsGroupNames = Recommendation::query()
            ->select('group_name')
            ->groupBy('group_name')
            ->get();

        return view('product::admin.recommendation.index', compact('recommendations', 'linkables', 'existsGroupNames'));
    }
    public function edit(Recommendation $recommendation)
    {
        $linkableData = (new LinkController)->create();
        $dataArray = $linkableData->getData(true);
        $linkables = $dataArray['data']['linkables'];

        return view('product::admin.recommendation.edit', compact('recommendation', 'linkables'));
    }

    public function store(RecommendationStoreRequest $request)
    {
        $recommendation = new Recommendation();
        $recommendation->fill($request->validated());
        $recommendation->save();
        $recommendation->storeFiles($request->images, Recommendation::COLLECTION_NAME_IMAGES);
        $recommendation->storeFiles($request->images_mobile, Recommendation::COLLECTION_NAME_IMAGES_MOBILE);
        ActivityLogHelper::storeModel('گروه محصول پیشنهادی محصول ثبت شد', $recommendation);

        return redirect()->back()->with('success', 'محصولات پیشنهادی با موفقیت ثبت شد.');
    }
    public function sort(Request $request)
    {
        Recommendation::setNewOrder($request->recommendations);

        return redirect()->route('admin.recommendations.index')
            ->with('success', 'محصولات پیشنهادی با موفقیت مرتب شد.');
    }

    public function update(RecommendationUpdateRequest $request, Recommendation $recommendation)
    {
        if ($request->linkable_id) {
            $recommendation->link = null;
        }
        if ($request->link) {
            $recommendation->linkable_id = null;
        }
        $recommendation->fill($request->validated());
        $recommendation->save();
        $recommendation->updateFiles($request->images, Recommendation::COLLECTION_NAME_IMAGES);
        $recommendation->updateFiles($request->images_mobile, Recommendation::COLLECTION_NAME_IMAGES_MOBILE);
        ActivityLogHelper::updatedModel('گروه محصول پیشنهادی محصول ثبت شد', $recommendation);

        return redirect()->route('admin.recommendations.index')->with('success', 'گروه محصول پیشنهادی با موفقیت به روزرسانی شد.');
    }

    public function destroy(Recommendation $recommendation)
    {
        if ($recommendation->recommendationItems->isNotEmpty()) {
            $recommendation->recommendationItems->delete();
        }
        $recommendation->delete();

        return redirect()->back()->with('success', 'گروه محصول پیشنهادی با موفقیت حذف شد.');
    }
}
