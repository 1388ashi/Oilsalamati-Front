<?php

namespace Modules\Dashboard\Services;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Customer\Entities\Customer;
use Modules\Home\Entities\SiteView;
use Modules\Order\Entities\Order;
use Modules\Report\Entities\MiniOrderReport;
use Modules\Report\Entities\OrderReport;
use Modules\Report\Entities\ProductReport;
use Modules\Report\Entities\VarietyReport;
//use Shetabit\Shopit\Modules\Dashboard\Services\ReportService as BaseReportService;
use Shetabit\Shopit\Modules\Report\Filters\AreaFilter;
use Shetabit\Shopit\Modules\Report\Filters\AttributeAndColorFilter;
use Shetabit\Shopit\Modules\Report\Filters\BrandFilter;
use Shetabit\Shopit\Modules\Report\Filters\BuyDateFilter;
use Shetabit\Shopit\Modules\Report\Filters\CategoriesFilter;
use Shetabit\Shopit\Modules\Report\Filters\CustomerBirthdateFilter;
use Shetabit\Shopit\Modules\Report\Filters\CustomerGenderFilter;
use Shetabit\Shopit\Modules\Report\Filters\CustomerIdFilter;
use Shetabit\Shopit\Modules\Report\Filters\ItemsCountFilter;
use Shetabit\Shopit\Modules\Report\Filters\StatusFilter;
use Shetabit\Shopit\Modules\Report\Filters\TotalFilter;
use Spatie\Activitylog\Models\Activity;

class ReportService /*extends BaseReportService*/
{
    protected function ordersStatusByDate(Carbon $startDate = null, Carbon $endDate = null)
    {
        $statuses = [];
        $orderQuery = Order::query()->whereNull('parent_id')->whereBetween('created_at', [$startDate, $endDate]);
        $statuses[Order::STATUS_NEW] = $orderQuery->clone()->where('status', Order::STATUS_NEW)->count();
        $statuses[Order::STATUS_RESERVED] = $orderQuery->clone()->where('status', Order::STATUS_RESERVED)->count();
        $statuses[Order::STATUS_WAIT_FOR_PAYMENT] = $orderQuery->clone()->where('status', Order::STATUS_WAIT_FOR_PAYMENT)->count();
        $statuses[Order::STATUS_CANCELED] = $orderQuery->clone()->where('status', Order::STATUS_CANCELED)->count();
        $statuses[Order::STATUS_DELIVERED] = $orderQuery->clone()->where('status', Order::STATUS_DELIVERED)->count();
        $statuses[Order::STATUS_IN_PROGRESS] = $orderQuery->clone()->where('status', Order::STATUS_IN_PROGRESS)->count();

        return $statuses;
    }

    public function productReport()
    {
        $hasActiveDiscount = \request()->boolean('has_active_discount');
        return ProductReport::query()->groupBy('id')
            ->when($hasActiveDiscount,function ($q) use($hasActiveDiscount){
                return $q->whereHas('product', function($q){
                    $q->where('discount', '>',0);
                });
            })
            ->addSelect([
                '*',
                'total_sale' => DB::raw('SUM(IF(amount < 0, -(amount * quantity), amount * quantity))
                 AS total_sale'),
                'sell_quantity' => DB::raw('SUM(quantity) AS sell_quantity'),
            ])->filters();
    }


    // came from vendor ================================================================================================
    public function __construct(protected $mode = 'online') {}

    public function getByYear($offsetYear = 0, $method = 'salesAmountByDate'): array
    {
        $currentMonth = verta()->subYears($offsetYear)->startYear()->startMonth();
        $yearStatistics = [];
        foreach (range(1, 12) as $month) {
            $l = $currentMonth->clone()->startMonth();
            $r = $currentMonth->clone()->endMonth();

            $yearStatistics[$month] = $this->$method(
                Carbon::instance($l->datetime()),
                Carbon::instance($r->datetime()),
            );
            $currentMonth = $currentMonth->addMonth();
        }

        return $yearStatistics;
    }

    public function getByMonth($month, $offsetYear = 0, $method = 'salesAmountByDate'): array
    {
        $currentDay = verta()->subYears($offsetYear)->month($month)->startMonth()->startDay();
        $monthStatistics = [];
        $totalDaysInMonth = (int)$currentDay->clone()->endMonth()->format('d');
        foreach (range(1, $totalDaysInMonth) as $day) {
            $l = $currentDay->clone()->startDay();
            $r = $currentDay->clone()->endDay();

            $monthStatistics[$day] = $this->$method(
                Carbon::instance($l->datetime()),
                Carbon::instance($r->datetime()),
            );
            $currentDay = $currentDay->addDay();
        }

        return $monthStatistics;
    }

    public function getByWeek($method = 'salesAmountByDate'): array
    {
        $currentDay = verta()->startWeek()->startDay();
        $weekStatistics = [];
        foreach (range(1, 7) as $day) {
            $l = $currentDay->clone()->startDay();
            $r = $currentDay->clone()->endDay();

            $weekStatistics[$day] = $this->$method(
                Carbon::instance($l->datetime()),
                Carbon::instance($r->datetime()),
            );
            $currentDay = $currentDay->addDay();
        }

        return $weekStatistics;
    }


    #[ArrayShape(['males_count' => "int", 'females_count' => "int", 'unknowns_count' => "int"])]
    public function getCustomersGender(): array
    {
        $malesCount = Customer::query()->where('gender', Customer::MALE)->count();
        $femalesCount = Customer::query()->where('gender', Customer::FEMALE)->count();
        $allCount = Customer::query()->count();

        return [
            'males_count' => $malesCount,
            'females_count' => $femalesCount,
            'unknowns_count' => $allCount - $femalesCount - $malesCount
        ];
    }

    public function getLogs($limit = 6): \Illuminate\Database\Eloquent\Collection|array
    {
        return Activity::query()->limit($limit)->latest('id')
            ->get(['id', 'subject_type', 'description', 'event']);
    }

    // گرفتن تعداد سفارشات بر اساس وضعیت در 7 روز هفته
    public function getOrdersByStatus(): array
    {
        $currentDay = verta()->startWeek();
        $weekStatistics = [];
        foreach (range(1, 7) as $day) {
            $l = $currentDay->clone()->startDay();
            $r = $currentDay->clone()->endDay();

            $weekStatistics[$day] = $this->ordersStatusByDate(
                Carbon::instance($l->datetime()),
                Carbon::instance($r->datetime()),
            );
            $currentDay = $currentDay->addDay();
        }

        return $weekStatistics;
    }

    public function salesAmountByDate(Carbon $startDate = null, Carbon $endDate = null)
    {
//        $dateFilter = $startDate && $endDate ? "and `order_items`.`created_at` > '{$startDate->toDateTimeString()}'
//         and `order_items`.`created_at` < '{$endDate->toDateTimeString()}'" : "";
//        $sums = \DB::select("select IFNULL(SUM((order_items.amount) * order_items.quantity) , 0) AS amount,
//       IFNULL(SUM(order_items.discount_amount * order_items.quantity) ,0) AS discount_amount,
//       IFNULL(SUM(order_items.quantity), 0) AS quantity from `order_items`
//           inner join `orders` on `orders`.`id` = `order_items`.`order_id`
//where `order_items`.`status` = 1 and `orders`.`status` not in ('canceled', 'wait_for_payment')" . $dateFilter);
        if ($this->mode === 'online') {
            return OrderReport::query()->success()->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>', $startDate);
            })->when($endDate, function ($query) use ($endDate) {
                $query->where('created_at', '<', $endDate);
            })->selectRaw('SUM(order_items_count) AS quantity, SUM(total) AS amount, SUM(shipping_amount) AS shipping_amount, SUM(discount_amount) AS discount_amount')->first();
        } else {
            return MiniOrderReport::query()->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>', $startDate);
            })->when($endDate, function ($query) use ($endDate) {
                $query->where('created_at', '<', $endDate);
            })->selectRaw('SUM(mini_order_items_count) AS quantity, SUM(total) AS amount, SUM(discount_amount) AS discount_amount')->first();

        }
    }

    public function getLastLogins()
    {
        return PersonalAccessToken::latest('id')
            ->select(['id', 'tokenable_id', 'tokenable_type', 'created_at'])
            ->take(4)->with('tokenable')->get();
    }

    public function customerReport($request)
    {
        $report = $this->baseOrderReport();

        return $report;
    }

    public function baseOrderReport(): \Illuminate\Database\Eloquent\Builder
    {
        $orderReportQuery = OrderReport::query()->addSelect(['*',
            'orders_count' => DB::raw('COUNT(order_reports_view.id) AS orders_count'),
            'total' => DB::raw('SUM(order_reports_view.total) AS _total'),
            'statuses' => DB::raw('GROUP_CONCAT(order_reports_view.status) AS statuses'),
            'order_ids' => DB::raw('GROUP_CONCAT(order_reports_view.id) AS order_ids'),
            'order_items_count' => DB::raw('SUM(order_reports_view.order_items_count) AS _order_items_count'),
            'product_ids' => DB::raw("REPLACE(GROUP_CONCAT(order_reports_view.product_ids SEPARATOR ','), ',,' COLLATE utf8mb4_unicode_ci, ',') AS _product_ids"),
            'color_ids' => DB::raw("REPLACE(GROUP_CONCAT(order_reports_view.color_ids SEPARATOR ','), ',,' COLLATE utf8mb4_unicode_ci, ',') AS _color_ids"),
            'attribute_ids' => DB::raw("REPLACE(GROUP_CONCAT(order_reports_view.attribute_ids SEPARATOR '!##!'), '!##!!##!' COLLATE utf8mb4_unicode_ci, '!##!') AS _attribute_ids"),
        ])->with('customer')->sortFilter();

        $this->applyOrderReportFilters($orderReportQuery);

        return $orderReportQuery;
    }

    protected function applyOrderReportFilters($query) {
        return app(Pipeline::class)->send($query)->through([
            AreaFilter::class,
            AttributeAndColorFilter::class,
            BrandFilter::class,
            BuyDateFilter::class,
            CategoriesFilter::class,
            CustomerIdFilter::class,
            CustomerBirthdateFilter::class,
            CustomerGenderFilter::class,
            ItemsCountFilter::class,
            TotalFilter::class,
            StatusFilter::class,
        ])->thenReturn();
    }

    public function varietyReport()
    {
        $baseQuery = VarietyReport::query()->withTrashed()->groupBy('id')
            ->addSelect([
                '*',
                'total_sale' => DB::raw('SUM(amount * quantity) AS total_sale'),
                'sell_quantity' => DB::raw('SUM(quantity) AS sell_quantity')
            ])->withCommonRelations()->filters();

        return $baseQuery;
    }

    public function orderReportByDate($startDate, $endDate)
    {
        $order = $this->baseOrderReport();

        $orderBuilder = $order->whereBetween('created_at', [$startDate, $endDate]);
        $customerBuilder = Customer::query()->whereBetween('created_at', [$startDate, $endDate]);
        $viewsBuilder = SiteView::query()->whereBetween('date', [$startDate, $endDate]);
        $date = [
            'total_amount' => $orderBuilder->sum('total'),
            'count_order' => $orderBuilder->count(),
            'count_customer' => $customerBuilder->count(),
            'count_views' => $viewsBuilder->sum('count'),
        ];

        return $date;
    }


}
