<?php

namespace Modules\Shipping\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Shipping\Http\Controllers\Admin\ShippingController as BaseShippingController;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Entities\City;
use Modules\Area\Entities\Province;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Http\Requests\Admin\ShippingCityAssignRequest;
use Modules\Shipping\Http\Requests\Admin\ShippingStoreRequest;
use Modules\Shipping\Http\Requests\Admin\ShippingUpdateRequest;
use Modules\Shipping\Services\ShippingCalculatorService;
use Modules\Shipping\Services\ShippingCollectionService;

class ShippingController extends Controller
{
    public function index()
    {
        $shippings = Shipping::query()
            ->latest('order')
            ->filters()
            ->paginate();


        if (request()->header('Accept') == 'application/json')
            return response()->success('', compact('shippings'));

        $totalShipping = $shippings->total();

        return view('shipping::admin.shipping.index', compact('shippings', 'totalShipping'));
    }

    public function create(): View
    {
        $provinces = Province::query()->select('id', 'name')->get();

        return view('shipping::admin.shipping.create', compact('provinces'));
    }


    public function store(ShippingStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $shipping = Shipping::create($request->all());

            if ($request->hasFile('logo')) {
                $shipping->storeFiles($request->images, 'logo');
            }
            $shipping->load('media');

            if ($request->provinces) {
                $shipping->setProvinces($request);
            }

            if ($request->customer_roles) {
                $shipping->setCustomerRoles($request);
            }

            DB::commit();
        } catch (Exception $exception) {

            DB::rollBack();
            Log::error($exception->getTraceAsString());

            if (request()->header('Accept') == 'application/json') {
                return response()->error('مشکلی در ثبت حمل و نقل به وجود آمده است: ' . $exception->getMessage(), $exception->getTrace());
            }

            return redirect()->back()->with('error', 'مشکلی در ثبت حمل و نقل به وجود آمده است');
        }
        $shipping->loadCommonRelations();
        ActivityLogHelper::storeModel(' سرویس حمل و نقل ثبت شد', $shipping);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('سرویس حمل و نقل با موفقیت ثبت شد', compact('shipping'));
        }

        return redirect()->back()->with('success', 'سرویس حمل و نقل با موفقیت ثبت شد');
    }


    public function show($id)
    {
        $shipping = Shipping::query()->findOrFail($id);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('سرویس حمل و نقل با موفقیت دریافت شد', compact('shipping'));
        }

        return view('shipping::admin.shipping.show', compact('shipping'));
    }


    public function sort(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|exists:shippings,id'
        ]);
        $order = 99;
        foreach ($request->input('orders') as $itemId) {
            $model = Shipping::find($itemId);
            if (!$model) {
                continue;
            }
            $model->order = $order--;
            $model->save();
        }

        return response()->success('مرتب سازی با موفقیت انجام شد');
    }

    public function edit(Shipping $shipping): View
    {
        $provinces = Province::query()->select('id', 'name')->get();
        // $shipping->load('shippingRanges');

        return view('shipping::admin.shipping.edit', compact(['shipping', 'provinces']));
    }

    public function update(ShippingUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            /** @var Shipping $shipping */
            $shipping = Shipping::findOrFail($id);

            $shipping->fill($request->all());
            if ($request->hasFile('logo')) {
                $shipping->updateFiles($request->images, 'logo');
            }
            $shipping->save();

            $shipping->setProvinces($request);
            $shipping->setCustomerRoles($request);


            DB::commit();
        } catch (Exception $exception) {

            DB::rollBack();
            Log::error($exception->getTraceAsString());

            if (request()->header('Accept') == 'application/json') {
                return response()->error('مشکلی در به روزرسانی حمل و نقل به وجود آمده است: ' . $exception->getMessage(), $exception->getTrace());
            }

            return redirect()->back()->with('error', 'مشکلی در به روزرسانی حمل و نقل به وجود آمده است');
        }

        ActivityLogHelper::updatedModel(' سرویس حمل و نقل ویرایش شد', $shipping);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('سرویس حمل و نقل با موفقیت به روزرسانی شد', compact('shipping'));
        }

        return redirect()->back()->with('success', 'سرویس حمل و نقل با موفقیت به روزرسانی شد');
    }


    public function assignCities(ShippingCityAssignRequest $request, $id)
    {
        /**
         * @var $shipping Shipping
         */
        $shipping = Shipping::findOrFail($id);
        $cities = [];
        foreach ($request->input('cities') ?? [] as $city) {
            $cityModel = City::find($city['id']);
            if (!$cityModel || !$shipping->provinces()->where('provinces.id', $cityModel->province_id)->exists()) {
                continue;
            }
            $cities[$city['id']] = [
                'price' => $city['price']
            ];
        }
        $shipping->cities()->sync($cities);

        return response()->success('عملیات با موفقیت انجام شد', compact('shipping'));
    }


    public function destroy($id)
    {
        $shipping = Shipping::findOrFail($id);

        $shipping->delete();
        ActivityLogHelper::deletedModel(' سرویس حمل و نقل حذف شد', $shipping);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('سرویس حمل و نقل با موفقیت حذف شد');
        }

        return redirect()->back()->with('success', 'سرویس حمل و نقل با موفقیت حذف شد');
    }

    public function ranges(Shipping $shipping)
    {
        $shippingRanges = $shipping->shippingRanges;

        return view('shipping::admin.shipping.ranges', compact('shippingRanges', 'shipping'));
    }

    public function getShippableForAddress($addresId) {

        /** @var Customer $user */
        $user = auth('customer')->user();

        $address = $user->addresses()->where('id', $addresId)->first();
        $shippings = (new ShippingCollectionService)->getShippableShippingsForAddress($address);

        foreach ($shippings as $shipping) {
            $calculatorService = new ShippingCalculatorService($address, $shipping, auth()->user(), $user->carts);
            $response = $calculatorService->calculate();
            if (is_array($response)) {
                $shipping->calculated_response = $response;
            }
        }

        return response()->success('', compact('shippings'));
    }
}
