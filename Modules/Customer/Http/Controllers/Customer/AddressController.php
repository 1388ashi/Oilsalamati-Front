<?php

namespace Modules\Customer\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Modules\Customer\Entities\Address;
use Modules\Customer\Http\Requests\Customer\AddressStoreRequest;
use Modules\Customer\Http\Requests\Customer\AddressUpdateRequest;
use Modules\Order\Entities\Order;
//use Shetabit\Shopit\Modules\Customer\Http\Controllers\Customer\AddressController as BaseAddressController;

use Illuminate\Routing\Controller;

class AddressController extends Controller
{
    public function index()
    {
        $customer = \Auth::guard('customer-api')->user();
        $addresses = $customer->addresses;
        return response()->success('لیست آدرس ها', compact('addresses'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param AddressStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AddressStoreRequest $request)
    {
        $customer = $request->user();
        $address = $customer->addresses()->create($request->all());
        $address->load('city.province');

        return response()->success('آدرس با موفقیت ثبت شد.', compact('address'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddressUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AddressUpdateRequest $request, Address $address)
    {
        if ($address->customer_id != auth()->user()->id) {
            return response()->error('Forbidden', [], 403);
        }

        $address->update($request->all());
        $address->load('city.province');

        return response()->success('آدرس با موفقیت به روزرسانی شد.', compact('address'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $address = Address::findOrFail($id);
        if ($address->customer_id != auth()->user()->id) {
            return response()->error('Forbidden', [], 403);
        }

        $address->delete();

        return response()->success('آدرس با موفقیت حذف شد.');
    }
}
