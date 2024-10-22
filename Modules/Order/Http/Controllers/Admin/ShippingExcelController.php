<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Order\Entities\ShippingExcel;
use Modules\Order\Imports\ShippingExcelImport;

class ShippingExcelController extends BaseController
{
	public function index()
	{
		$shippingExcels = ShippingExcel::latest()
      ->filters()
			->latest('id')
			->paginate();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('', ['shipping_excels' => $shippingExcels]);
		}

		return view('order::admin.shipping-excel.index', compact('shippingExcels'));
	}

	public function store(Request $request)
	{
		$request->validate([
			'file' => 'required'
		]);
		Excel::import(new ShippingExcelImport, $request->file('file'));

		if (request()->header('Accept') == 'application/json') {
			return response()->success('با موفقیت اضافه شد');
		}

		return redirect()->back()->with('succes', 'با موفقیت اضافه شد');
	}

	public function destroy($id)
	{
		$shippingExcel = ShippingExcel::query()->findOrFail($id);
		$shippingExcel->delete();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('');
		}

		return redirect()->back()->with('succes', 'با موفقیت ویرایش شد');
	}

	public function multipleDelete(Request $request)
	{
		$ids = $request->ids;
		DB::table("shipping_excels")->whereIn('id', explode(",", $ids))->delete();

		if (request()->header('Accept') == 'application/json') {
			return response()->json(['status' => true, 'message' => "گزینه های انتخابی با موفقیت حذف شدند! "]);
		}

		return redirect()->back()->with('succes', 'گزینه های انتخابی با موفقیت حذف شدند');
	}
}
