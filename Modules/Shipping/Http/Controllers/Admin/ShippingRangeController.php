<?php

namespace Modules\Shipping\Http\Controllers\Admin;

use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Shipping\Entities\ShippingRange;
use Modules\Shipping\Http\Requests\Admin\ShippingRangeRequest;

class ShippingRangeController extends BaseController
{
    public function index()
    {
        $shippingRanges = ShippingRange::query()->orderBy('lower', 'ASC')
            ->filters()->paginateOrAll();

        return response()->success('', [
            'shipping_ranges' => $shippingRanges
        ]);
    }

    public function store(ShippingRangeRequest $request)
    {
        $shippingRange = ShippingRange::store($request);
        ActivityLogHelper::storeModel(' بازه حمل و نقل ثبت شد', $shippingRange);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('بازه با موفقیت ایجاد شد', [
                'shipping_range' => $shippingRange
            ]);
        }

        return redirect()->back()->with('success', 'بازه با موفقیت ایجاد شد');
    }

    public function update(ShippingRangeRequest $request, ShippingRange $shippingRange)
    {
        $shippingRange->update($request->all());
        ActivityLogHelper::storeModel(' بازه حمل و نقل ویرایش شد', $shippingRange);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('بازه با موفقیت بروزرسانی شد', [
                'shipping_range' => $shippingRange
            ]);
        }

        return redirect()->back()->with('success', 'بازه با موفقیت بروزرسانی شد');
    }

    public function show(ShippingRange $shippingRange)
    {
        return response()->success('', [
            'shipping_range' => $shippingRange
        ]);
    }

    public function destroy(ShippingRange $shippingRange)
    {
        $shippingRange->delete();
        ActivityLogHelper::storeModel(' بازه حمل و نقل حذف شد', $shippingRange);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('بازه با موفقیت حذف شد');
        }

        return redirect()->back()->with('success', 'بازه با موفقیت حذف شد');
    }
}
