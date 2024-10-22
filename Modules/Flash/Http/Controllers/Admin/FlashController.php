<?php

namespace Modules\Flash\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Flash\Entities\Flash;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Flash\Http\Requests\Admin\FlashStoreRequest;
use Modules\Flash\Http\Requests\Admin\FlashUpdateRequest;
use Modules\Product\Entities\Product;
// use Shetabit\Shopit\Modules\Flash\Http\Controllers\Admin\FlashController as BaseFlashController;
use Modules\Product\Entities\Recommendation;
use Modules\Product\Jobs\SendProductDiscountNotificationJob;

class FlashController extends Controller
{

	public function index()
	{
		$flashes = Flash::query()
			->latest('id')
			->filters()
			->when(request('active'), function (Builder $query) {
				$now = Carbon::now();
				return $query->whereDate('start_date', '>=', $now)
					->whereDate('end_date', '<=', $now)
					->where('status', '=', 1);
			})
			->paginate();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('Get all flashes', compact('flashes'));
		}
		$flashesCount = $flashes->total();

		return view('flash::admin.index', compact('flashes', 'flashesCount'));
	}

	public function create()
	{
		$products = Product::query()->select(['id', 'title'])->active()->latest('id')->get();

		return view('flash::admin.create', compact('products'));
	}

	public function store(FlashStoreRequest $request)
	{
		DB::beginTransaction();
		try {
			/** @var Flash $flash */
			$flash = Flash::create($request->all());



			//Media
			if ($request->hasFile('image')) {
                $flash->storeFiles($request->images, Flash::COLLECTION_NAME_IMAGES);
			}
			if ($request->hasFile('mobile_image')) {
                $flash->storeFiles($request->images_mobile, Flash::COLLECTION_NAME_IMAGES_MOBILE);
            }
			if ($request->hasFile('bg_image')) {
				$flash->addBackgroungImage($request->file('bg_image'));
			}

			$flash->load('media');

			//Attach products
			foreach ($request->products as $product) {

				$flash->products()->attach($product['id'], [
					'discount_type' => $product['discount_type'] ?? $request->input('discount_type'),
					'discount' => $product['discount'] ?? $request->input('discount'),
					'salable_max' => $product['quantity'] ?? PHP_INT_MAX
				]);

				$zproduct = Product::find($product['id']);
				if ($zproduct) {
					SendProductDiscountNotificationJob::dispatch($zproduct);
				}
			}

			foreach ($request->products as $product) {
				if (1) { // is_null($product['quantity'])
					$baseProduct = Product::query()->findOrFail($product['id']);
					//calculate balance
					$balance = 0;
					$varieties = $baseProduct->varieties;
					foreach ($varieties as $variety) {
						$balance += $variety->store->balance ?? 0;
					}
					$products[$product['id']] = [
						'salable_max' => $balance,
					];
				}
			}
			$flash->products()->sync($products);


			DB::commit();
		} catch (Exception $exception) {
			DB::rollBack();
			Log::error($exception->getTraceAsString());

			if (request()->header('Accept') == 'application/json') {
				return response()->error('مشکلی در ثبت فروش ویژه به وجود آمده است: ' . $exception->getMessage(), $exception->getTrace());
			}

			return redirect()->route('admin.flashes.index')->with('error', 'مشکلی در ثبت فروش ویژه به وجود آمده است');
		}

        ActivityLogHelper::storeModel(' فروش ویژه ثبت شد', $flash);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('فروش ویژه با موفقیت ثبت شد', compact('flash'));
		}

		return redirect()->route('admin.flashes.index')->with('success', 'فروش ویژه با موفقیت ثبت شد');
	}

	public function edit(Flash $flash)
	{
		$products = Product::query()->select(['id', 'title'])->active()->latest('id')->get();
		$flash->load('products');

		return view('flash::admin.edit', compact('products', 'flash'));
	}

	public function update(FlashUpdateRequest $request, $id)
	{
		DB::beginTransaction();
		try {

			$flash = Flash::findOrFail($id);

			$flash->fill($request->all());
			$flash->save();
			if ($request->hasFile('image')) {
                $flash->updateFiles($request->images, Flash::COLLECTION_NAME_IMAGES);
			}
			if ($request->hasFile('mobile_image')) {
                $flash->updateFiles($request->images_mobile, Flash::COLLECTION_NAME_IMAGES_MOBILE);
			}
			if ($request->hasFile('bg_image')) {
				$flash->addBackgroungImage($request->bg_image);
			}
			//sync products
			$products = [];
			foreach ($request->products as $product) {

				$baseProduct = Product::query()->findOrFail($product['id']);
				//calculate balance
				$balance = 0;
				$varieties = $baseProduct->varieties;
				foreach ($varieties as $variety) {
					$balance += $variety->store->balance ?? 0;
				}
				$products[$product['id']] = [
					'discount_type' => $product['discount_type'] ?? $request->input('discount_type'),
					'discount' => $product['discount'] ?? $request->input('discount'),
					'salable_max' => $balance
				];
			}

			$flash->products()->sync($products);
			DB::commit();
		} catch (Exception $exception) {
			DB::rollBack();
			Log::error($exception->getTraceAsString());

			if (request()->header('Accept') == 'application/json') {
				return response()->error('مشکلی در به روزرسانی فروش ویژه به وجود آمده است: ' . $exception->getMessage(), $exception->getTrace());
			}

			return redirect()->route('admin.flashes.index')->with('error', 'مشکلی در به روزرسانی فروش ویژه به وجود آمده است');
		}

        ActivityLogHelper::updatedModel(' فروش ویژه بروز شد', $flash);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('فروش ویژه با موفقیت به روزرسانی شد', compact('flash'));
		}

		return redirect()->route('admin.flashes.index')->with('success', 'فروش ویژه با موفقیت به روزرسانی شد');
	}





	// came from vendor ================================================================================================
	/**
	 * Show the specified resource.
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse|View
	 */
	public function show($id)
	{
		$flash = Flash::findOrFail($id);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('فروش ویژه با موفقیت دریافت شد', compact('flash'));
		}

		return view('flash::admin.show', compact('flash'));
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse|RedirectResponse
	 */
	public function destroy($id)
	{
		$flash = Flash::findOrFail($id);

		$flash->delete();
        ActivityLogHelper::deletedModel(' فروش ویژه حذف شد', $flash);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('فروش ویژه با موفقیت حذف شد');
		}

		return redirect()->route('admin.flashes.index')->with('success', 'فروش ویژه با موفقیت حذف شد');
	}
}
