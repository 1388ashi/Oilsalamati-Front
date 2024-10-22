<?php

namespace Modules\Specification\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Specification\Http\Controllers\Admin\SpecificationController as BaseSpecificationController;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Specification\Entities\Specification;
use Modules\Specification\Http\Requests\Admin\SpecificationSortRequest;
use Modules\Specification\Http\Requests\Admin\SpecificationStoreRequest;
use Modules\Specification\Http\Requests\Admin\SpecificationUpdateRequest;

class SpecificationController extends Controller
{
  /**
   * Display a listing of the resource.
   * @return JsonResponse|View
   */
  public function index()
  {
    $specifications = Specification::query()
      ->orderByDesc('order')
      ->latest('id')
      ->filters()
      ->paginate();

    if (request()->header('Accept') == 'application/json') {
      return response()->success('دریافت لیست مشخصه ها', compact('specifications'));
    }

    return view('specification::admin.index', compact('specifications'));
  }

  public function create()
  {
    $types = Specification::getAvailableTypes();

    return view('specification::admin.create', compact('types'));
  }

  /**
   * Store a newly created resource in storage.
   * @param SpecificationStoreRequest $request
   * @return JsonResponse|RedirectResponse
   */
  public function store(SpecificationStoreRequest $request)
  {
    $specification = Specification::create($request->all());

    if (in_array($request->type, [Specification::TYPE_SELECT, Specification::TYPE_MULTI_SELECT]) && $request->values) {
      foreach ($request->values as $value) {
        $specification->values()->create([
          'value' => $value
        ]);
      }
    }

      ActivityLogHelper::storeModel(' مشخصه ثبت شد', $specification);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشخصه با موفقیت ثبت شد', compact('specification'));
    }

    return redirect()->route('admin.specifications.index')->with([
      'success' => 'مشخصه جدید با موفقیت ثبت شد'
    ]);

  }

  /**
   * Show the specified resource.
   * @param int $id
   * @return JsonResponse|View
   */
  public function show($id)
  {
    $specification = Specification::findOrfail($id);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشخصه با موفقیت دریافت شد', compact('specification'));
    }

    return view('specification::admin.show', compact('specification'));
  }

  public function edit(Specification $specification)
  {
    $types = Specification::getAvailableTypes();
    $specification->load('values');

    return view('specification::admin.edit', compact(['specification', 'types']));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param SpecificationUpdateRequest $request
   * @param int $id
   * @return JsonResponse|RedirectResponse
   */
  public function update(SpecificationUpdateRequest $request, $id)
  {
    $specification = Specification::with('values')->findOrfail($id);
    $specification->update($request->all());
    if (in_array($request->type, [Specification::TYPE_SELECT, Specification::TYPE_MULTI_SELECT]) && $request->values) {
      $notDeleteValues = [];
      foreach ($request->values as $value) {
        /**
         * @var $specification Collection
         */
        $specValue = $specification->values->where('id', $value)->first();
        $specValue = $specValue ?: $specification->values->where('value', $value)->first();
        if ($specValue) {
          $notDeleteValues[] = $specValue->id;
          continue;
        }
        $v = $specification->values()->create([
          'value' => $value
        ]);
        $notDeleteValues[] = $v->id;
      }
      $specification->values()->whereNotIn('id', $notDeleteValues)->delete();
      $specification->load('values');
    }

      ActivityLogHelper::updatedModel(' مشخصه ویرایش شد', $specification);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشخصه با موفقیت به روزرسانی شد', compact('specification'));
    }

    return redirect()->route('admin.specifications.index')->with([
      'success' => 'مشخصه با موفقیت ویرایش شد'
    ]);
  }

  public function sort(SpecificationSortRequest $request)
  {
    $order = 999999;
    foreach ($request->ids as $id) {
      $specification = Specification::find($id);
      if (!$specification) continue;
      $specification->order = $order--;
      $specification->save();
    }

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مرتب سازی با موفقیت انجام شد');
    }

  }

  /**
   * Remove the specified resource from storage.
   * @param int $id
   * @return JsonResponse|RedirectResponse
   */
  public function destroy($id)
  {
    $specification = Specification::findOrfail($id);

    $specification->delete();
      ActivityLogHelper::deletedModel(' مشخصه حذف شد', $specification);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشخصه با موفقیت حذف شد', compact('specification'));
    }

    return redirect()->back()->with('success', 'مشخصه با موفقیت حذف شد');
  }
}

