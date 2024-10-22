<?php

namespace Modules\Coupon\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Category\Entities\Category;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Http\Requests\Admin\CouponStoreRequest;
use Modules\Coupon\Http\Requests\Admin\CouponUpdateRequest;
// use Shetabit\Shopit\Modules\Coupon\Http\Controllers\Admin\CouponController as BaseCouponController;

class CouponController extends Controller
{

	public function index(): JsonResponse|View
	{
		$couponsQuery = Coupon::query()
			->withCount('customers')
			->with('categories')
			->SearchKeyword()
			->latest()
			->FilterByType()
			->filters();

		$icoupons = Coupon::query()->withCount('customers')->get();


		$totals['coupon']['label'] = 'تعداد کدهای تخفیف';
		$totals['coupon']['count'] = $icoupons->count();

		$totals['GiftCoupons']['label'] = 'تعداد کدهای تخفیف سفارش جدید';
		$totals['GiftCoupons']['count'] = (clone $couponsQuery)->where('coupon_type', Coupon::ORDER_GIFT_COUPON)->count();

		$request = request();
		Helpers::toCarbonRequest(['start_date', 'end_date'], $request);

		$totals['GiftCoupons']['used'] = Coupon::query()->where('coupon_type', Coupon::ORDER_GIFT_COUPON)->leftJoinSub(DB::table('coupon_customer')
			->when($request->filled('start_date'), fn($q) => $q->where('created_at', '>', $request->start_date))
			->when($request->filled('end_date'), fn($q) => $q->where('created_at', '<', $request->end_date))
			->select(['id', 'coupon_id', 'customer_id']), 'coupon_customer', function ($join) {
			$join->on('coupons.id', '=', 'coupon_customer.coupon_id');
		})->select(DB::raw('count(coupon_customer.id) as total_used'))->pluck('total_used')[0];
		$totals['AdminCoupons']['label'] = 'تعداد کدهای تخفیف ادمین';
		$totals['AdminCoupons']['count'] = $icoupons->where('coupon_type', Coupon::ADMIN_COUPON)->count();

		$totals['lastThirtyDaysTotalGiftCoupons']['label'] = 'تعداد کدهای تخفیف سفارش جدید ساخته شده در 30روز گذشته';
		$totals['lastThirtyDaysTotalGiftCoupons']['count'] = $icoupons->where('coupon_type', Coupon::ORDER_GIFT_COUPON)->where('created_at', '>=', now()->subDays(30))->count();

		$totals['lastThirtyDaysUsedTotalGiftCoupons']['label'] = 'تعداد کدهای تخفیف استفاده شده سفارش جدید در 30روز گذشته';
		$totals['lastThirtyDaysUsedTotalGiftCoupons']['count'] = $icoupons->where('coupon_type', Coupon::ORDER_GIFT_COUPON)->where('created_at', '>=', now()->subDays(30)->endOfDay())->where('total_usage', '!=', 0)->count();


		$coupons = $couponsQuery->paginate();
		$coupons->each(function (Coupon $coupon) {
			$coupon->append('total_usage');
		});

		if (request()->header('Accept') == 'application/json') {
			return response()->success('لیست تمام کد تخفیف ها.', compact('coupons', 'totals',));
		}

		return view('coupon::admin.index', compact(['coupons', 'totals']));
	}

	public function create()
	{
		return view('coupon::admin.create');
	}

	public function store(CouponStoreRequest $request, Coupon $coupon): JsonResponse|RedirectResponse
	{
		Helpers::toCarbonRequest(['start_date', 'end_date'], $request);

		$coupon->fill($request->all())->save();

		if ($request->has('categories') && $request->categories) {
			// coupon with category
			$categoriesForSave = [];
			foreach ($request->categories as $requestCategories) {
				$categoriesForSave[] = [
					'category_id' => $requestCategories['id'],
					'coupon_id' => $coupon->id,
					'amount' => $requestCategories['amount'],
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
			$coupon->categories()->attach($categoriesForSave);
			$coupon->load('categories');
		}

        ActivityLogHelper::storeModel(' کد تخفیف ثبت شد', $coupon);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('کد تخفیف با موفقیت ایجاد شد.', compact('coupon'));
		}

		return redirect()->route('admin.coupons.index')->with('success', 'کد تخفیف با موفقیت ایجاد شد');
	}

	public function edit(Coupon $coupon)
	{
		return view('coupon::admin.edit', compact('coupon'));
	}

	public function update(CouponUpdateRequest $request, Coupon $coupon): JsonResponse|RedirectResponse
	{
		Helpers::toCarbonRequest(['start_date', 'end_date'], $request);

		$coupon->update($request->all());


		if ($request->has('categories') && $request->categories) {
			// coupon with category
			$categoriesForSave = [];
			foreach ($request->categories as $requestCategories) {
				$categoriesForSave[] = [
					'category_id' => $requestCategories['id'],
					'coupon_id' => $coupon->id,
					'amount' => $requestCategories['amount'],
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
			$coupon->categories()->sync($categoriesForSave);
			$coupon->load('categories');
		}

        ActivityLogHelper::updatedModel(' کد تخفیف بروزرسانی شد', $coupon);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('کد تخفیف با موفقیت بروزرسانی شد.', compact('coupon'));
		}

		return redirect()->route('admin.coupons.index')->with('success', 'کد تخفیف با موفقیت بروزرسانی شد');
	}



	// came from vendor ================================================================================================
	/**
	 * Show the specified resource.
	 * @param Coupon $coupon
	 * @return JsonResponse
	 */
	public function show(Coupon $coupon): JsonResponse|View
	{
		if (request()->header('Accept') == 'application/json') {
			return response()->success('کد تخفیف با موفقیت بروزرسانی شد.', compact('coupon'));
		}

		return view('coupon::admin.show', compact('coupon'));
	}

	/**
	 * Remove the specified resource from storage.
	 * @param Coupon $coupon
	 * @return JsonResponse|RedirectResponse
	 */
	public function destroy(Coupon $coupon): JsonResponse|RedirectResponse
	{
		$coupon->delete();
        ActivityLogHelper::deletedModel(' کد تخفیف حذف شد', $coupon);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('کد تخفیف با موفقیت حذف شد.', compact('coupon'));
		}

		return redirect()->route('admin.coupons.index')->with('success', 'کد تخفیف با موفقیت حذف شد');
	}
}
