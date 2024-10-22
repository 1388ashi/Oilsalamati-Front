<?php

namespace Modules\Area\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Modules\Area\Entities\Province;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Http\Requests\Admin\ProvinceStoreRequest;
use Modules\Area\Http\Requests\Admin\ProvinceUpdateRequest;

class ProvinceController extends Controller
{
  public function index(): View|JsonResponse
  {
    $provinces = Province::query()->filters()->latest('id')->paginate();

    if (request()->header('Accept') == 'application/json') {
      return response()->success('', compact('provinces'));
    }

    return view('area::admin.province.index', compact('provinces'));
  }

  public function show(int $id): View|JsonResponse
  {
    $province = Province::with('cities')->findOrFail($id);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('', compact('province'));
    }

    return view('area::admin.province.show', compact('province'));
  }

  public function store(ProvinceStoreRequest $request): JsonResponse|RedirectResponse
  {
    $province = Province::query()->create($request->validated());
    ActivityLogHelper::simple('استان ثبت شد', 'store', $province);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('استان با موفقیت ایجاد شد', compact('province'));
    }

    return redirect()->back()->with('success', 'استان با موفقیت ثبت شد.');
  }

  public function update(ProvinceUpdateRequest $request, int $id): JsonResponse|RedirectResponse
  {
    $province = Province::query()->findOrFail($id);
    $province->update($request->all());
    ActivityLogHelper::updatedModel('استان بروز شد', $province);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('استان با موفقیت بروز شد', compact('province'));
    }

    return redirect()->back()->with('success', 'استان با موفقیت ویرایش شد.');
  }

  public function destroy(int $id): JsonResponse|RedirectResponse
  {
    $province = Province::find($id);
    ActivityLogHelper::deletedModel('استان حذف شد', $province);
    $province = Province::destroy($id);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('استان با موفقیت حذف شد', compact('province'));
    }
    return redirect()->back()->with('success', 'استان با موفقیت حذف شد.');
  }
}
