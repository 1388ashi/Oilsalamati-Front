<?php

namespace Modules\Specification\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Specification\Http\Controllers\Admin\SpecificationValueController as BaseSpecificationValueController;

use Illuminate\Routing\Controller;
use Modules\Specification\Entities\SpecificationValue;
use Modules\Specification\Http\Requests\Admin\SpecificationValueUpdateRequest;

class SpecificationValueController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param SpecificationValueUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SpecificationValueUpdateRequest $request, $id)
    {
        $specificationValue = SpecificationValue::findOrFail($id);
        $specificationValue->update(['value' => $request->value]);

        return response()->success('مقدار مشخصه با موفقیت به روزرسانی شد', $specificationValue);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $specificationValue = SpecificationValue::findOrFail($id);
        $specificationValue->delete();

        return response()->success('مقدار مشخصه با موفقیت حذف شد');
    }
}
