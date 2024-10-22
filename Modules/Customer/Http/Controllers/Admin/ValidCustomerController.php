<?php

namespace Modules\Customer\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Customer\Entities\ValidCustomer;
use Modules\Customer\Http\Requests\Admin\ValidCustomerStoreRequest;
use Modules\Customer\Http\Requests\Admin\ValidCustomerUpdateRequest;

class ValidCustomerController extends Controller
{
	public function index()
	{
		$customers = ValidCustomer::query()
			->latest('id')
			->searchkeywords()
			->paginate(10);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مشتریان معتبر', compact('customers'));
		}

		$customersCount = $customers->total();

		return view('customer::admin.valid-customer.index', compact(['customers', 'customersCount']));
	}

	public function create()
	{
		return view('customer::admin.valid-customer.create');
	}

	public function store(ValidCustomerStoreRequest $request)
	{
		$customer = ValidCustomer::create($request->all());
		$customer->uploadImage($request);
        ActivityLogHelper::storeModel(' مشتری معتبر ثبت شد', $customer);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مشتری با موفقیت ساخته شد', compact('customer'));
		}

		return redirect()->route('admin.valid-customers.index')->with('success', 'مشتری با موفقیت ساخته شد');
	}

	public function show($id)
	{
		$customer = ValidCustomer::findOrFail($id);

		return response()->success('مشتری معتبر', compact('customer'));
	}

	public function edit($id)
	{
		$customer = ValidCustomer::findOrFail($id);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مشتری معتبر', compact('customer'));
		}

		return view('customer::admin.valid-customer.edit', compact('customer'));
	}

	public function update(ValidCustomerUpdateRequest $request, $id)
	{
		$customer = ValidCustomer::findOrFail($id);
		$customer->update($request->all());
		$customer->uploadImage($request);
        ActivityLogHelper::updatedModel(' مشتری معتبر بروز شد', $customer);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مشتری با موفقیت ویرایش شد', compact('customer'));
		}

		return redirect()->route('admin.valid-customers.index')->with('success', 'مشتری با موفقیت ویرایش شد');
	}

	public function destroy($id)
	{
		$customer = ValidCustomer::findOrFail($id);

		$customer->deleteImage();
		$customer->delete();
        ActivityLogHelper::deletedModel(' مشتری معتبر حذف شد', $customer);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مشتری با موفقیت حذف شد');
		}

		return redirect()->back()->with('success', 'مشتری با موفقیت حذف شد');
	}
}
