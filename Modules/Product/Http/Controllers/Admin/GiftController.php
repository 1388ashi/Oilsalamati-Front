<?php

namespace Modules\Product\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Controllers\Admin\GiftController as BaseGiftController;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Entities\Gift;
use Modules\Product\Entities\Recommendation;
use Modules\Product\Http\Requests\Admin\GiftStoreRequest;
use Modules\Product\Http\Requests\Admin\GiftUpdateRequest;

class GiftController extends Controller
{

    public function index(): JsonResponse
    {
        $gifts = Gift::query()->with(['varieties', 'products'])->latest()->filters()->paginateOrAll();

        return response()->success('', compact('gifts'));
    }

    public function show($id): JsonResponse
    {
        $gift = Gift::query()->whereKey($id)->with(['varieties', 'products'])->first();

        return response()->success('', compact('gift'));
    }

    public function store(GiftStoreRequest $request): JsonResponse
    {
        $gift = new Gift;
        $gift->fill($request->all());
        $gift->save();
        $gift->storeFiles($request->images, Gift::COLLECTION_NAME_IMAGES);

        return response()->success('هدیه با موفقیت ایجاد شد', compact('gift'));
    }

    public function update(GiftUpdateRequest $request, Gift $gift): JsonResponse
    {
        $gift->fill($request->all());
        $gift->save();
        $gift->updateFiles($request->images, Gift::COLLECTION_NAME_IMAGES);

        return response()->success('هدیه با موفقیت ویرایش شد', compact('gift'));
    }

    public function destroy(Gift $gift): JsonResponse
    {
        $gift->delete();

        return response()->success('هدیه با موفقیت حذف شد', compact('gift'));
    }
}
