<?php

namespace Modules\Customer\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Customer\Http\Controllers\Admin\AddressController as BaseAddressController;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Entities\City;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Http\Requests\Admin\AddressStoreRequest;
use Modules\Customer\Http\Requests\Admin\AddressUpdateRequest;

class AddressController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param AddressStoreRequest $request
     */
    public function store(AddressStoreRequest $request)
    {
        $customer = Customer::query()->find($request->customer_id);
        $address = Address::query()->create([
          'city_id' => $request->input('city_id'),
          'first_name' => $request->input('first_name'),
          'last_name' => $request->input('last_name'),
          'address' => $request->input('address'),
          'customer_id' => $customer->id,
          'postal_code' => $request->input('postal_code'),
          'mobile' => $request->input('mobile'),
          'telephone' => $request->input('telephone'),
          'latitude' => $request->input('latitude'),
          'longitude' => $request->input('longitude'),
        ]);
        ActivityLogHelper::storeModel('آدرس ثبت شد', $address);
        $address->load(['city.province','customer']);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('آدرس با موفقیت ثبت شد.', compact('address'));
        }
        return redirect()->route('admin.customers.show',$customer)
        ->with('success', 'آدرس با موفقیت ثبت شد.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddressUpdateRequest $request
     * @param int $id
     */
    public function update(AddressUpdateRequest $request, $id)
    {
        $customer = Customer::query()->findOrFail($request->customer_id);
        $address = $customer->addresses()->findOrFail($id);
        ActivityLogHelper::updatedModel('آدرس بروز شد', $address);
        $address->update($request->all());
        $address->load(['customer']);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('آدرس با موفقیت به روزرسانی شد.', compact('address'));
          }
          return redirect()->route('admin.customers.show',$customer->id)
          ->with('success', 'آدرس با موفقیت به روزرسانی شد.');
    }
    public function getCities(Request $request)
    {
        $cities = City::where('province_id', $request->provinceId)->get();

        return response()->json($cities);
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy($customerId, $addressId)
    {
        $customer = Customer::query()->findOrFail($customerId);
        $address = $customer->addresses()->findOrFail($addressId);

        $address->delete();
        ActivityLogHelper::deletedModel('آدرس حذف شد', $address);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('آدرس با موفقیت حذف شد.');
        }
        return redirect()->route('admin.customers.show',$customer)
        ->with('success', 'آدرس با موفقیت ثبت شد.');
    }
}
