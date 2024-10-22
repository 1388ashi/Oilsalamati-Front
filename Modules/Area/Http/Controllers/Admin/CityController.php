<?php

namespace Modules\Area\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Entities\City;
use Modules\Area\Entities\Province;
use Modules\Area\Http\Requests\Admin\CityStoreRequest;
use Modules\Area\Http\Requests\Admin\CityUpdateRequest;

class CityController extends Controller
{
  public function index(): View|JsonResponse
  {
    $cities = City::latest('id')->filters()->paginate();

    if (request()->header('Accept') == 'application/json') {
      return response()->success('', compact('cities'));
    }

    $provinces = Province::query()->select('id', 'name')->active()->get();

    return view('area::admin.city.index', compact('cities', 'provinces'));
  }

  public function show(int $id): View|JsonResponse
  {
    $city = City::findOrFail($id);

    return response()->success('', compact('city'));
  }

  /**
   * Store a newly created resource in storage
   * @param CityStoreRequest $request
   * @return JsonResponse|RedirectResponse
   */
  public function store(CityStoreRequest $request): JsonResponse|RedirectResponse
  {
    $city = City::query()->create($request->validated());

    ActivityLogHelper::simple('شهر ثبت شد', 'store', $city);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('شهر با موفقیت ایجاد شد', compact('city'));
    }

    return redirect()->back()->with('success', 'شهر با موفقیت ثبت شد.');
  }


  /**
   * Update the specified resource in storage.
   * @param CityUpdateRequest $request
   * @param int $id
   * @return JsonResponse|RedirectResponse
   */
  public function update(CityUpdateRequest $request, int $id)
  {
    $city = City::query()->findOrFail($id);
    $city->update($request->validated());
    ActivityLogHelper::updatedModel('شهر بروزرسانی شد', $city);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('شهر با موفقیت بروز شد', compact('city'));
    }

    return redirect()->back()->with('success', 'شهر با موفقیت بروز شد.');
  }

  /**
   * Update the specified resource in storage.
   * @param int $id
   * @return JsonResponse|RedirectResponse
   */
  public function destroy(int $id)
  {
    $city = City::find($id);
    ActivityLogHelper::deletedModel('شهر حذف شد', $city);
    $city = City::destroy($id);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('شهر با موفیت حذف شد', compact('city'));
    }

    return redirect()->back()->with('success', 'شهر با موفیت حذف شد.');
  }
}
