<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Order\Entities\OrderGiftRange;
use Modules\Order\Http\Requests\Admin\OrderGiftRangeStoreRequest;
use Modules\Order\Http\Requests\Admin\OrderGiftRangeUpdateRequest;

class OrderGiftRangeController extends Controller
{
	public function index()
	{
		$gifts = OrderGiftRange::query()
			->select(['id', 'title', 'price', 'min_order_amount', 'description', 'created_at'])
			->latest('id')
			->paginate();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('', compact('gifts'));
		}
		return view('order::admin.order-gift-range.index', compact('gifts'));
	}

	public function create()
	{
		return view('order::admin.order-gift-range.create');
	}

	public function store(OrderGiftRangeStoreRequest $request)
	{
		$gift = OrderGiftRange::create($request->validated());
		$gift->uploadImage($request);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('هدیه با موفقیت ساخته شد', compact('gift'));
		}

        ActivityLogHelper::storeModel(' هدیه ثبت شد', $gift);

		return redirect()->route('admin.order-gift-ranges.index')->with('success', 'هدیه با موفقیت ساخته شد');
	}


	public function show($id)
	{
		$gift = OrderGiftRange::findOrFail($id);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('', compact('gift'));
		}
	}

	public function edit(OrderGiftRange $orderGiftRange)
	{
		return view('order::admin.order-gift-range.edit', compact('orderGiftRange'));
	}

	public function update(OrderGiftRangeUpdateRequest $request, $id)
	{
		$gift = OrderGiftRange::findOrFail($id);
		$gift->update($request->validated());
//		$gift->uploadImage($request);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('', compact('gift'));
		}

        ActivityLogHelper::updatedModel(' هدیه ویرایش شد', $gift);

		return redirect()->route('admin.order-gift-ranges.index')->with('success', 'هدیه با موفقیت ویرایش شد');
	}


	#TODO
	public function destroy($id)
	{
		$gift = OrderGiftRange::findOrFail($id);
		$gift->delete();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('', compact('gift'));
		}

        ActivityLogHelper::deletedModel(' هدیه حذف شد', $gift);

		return redirect()->route('admin.order-gift-ranges.index')->with('success', 'هدیه با موفقیت حذف شد');

	}
}
