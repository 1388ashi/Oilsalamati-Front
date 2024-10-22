<?php

namespace Modules\Admin\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Comment\Entities\Comment;
use Modules\Dashboard\Services\ReportService;
use Modules\Home\Entities\SiteView;
use Modules\Order\Entities\Order;
use Carbon\Carbon;
use Modules\ProductComment\Entities\ProductComment;
use Modules\Report\Entities\MiniOrderReport;
use Modules\Report\Entities\OrderReport;
use Spatie\Activitylog\Models\Activity;


class DashboardController extends Controller
{
    public function __construct(protected ReportService $reportService) {}
    public function index(){
        $order =  Order::query()->whereNull('parent_id')->select('id', 'status')
        ->whereNotIn('status', [Order::STATUS_CANCELED, Order::STATUS_WAIT_FOR_PAYMENT, Order::STATUS_FAILED]);
        $ordersCount = $order->whereNull('parent_id')->count();
        $totalSalesToday = Order::whereDate('created_at', Carbon::today())->sum('total_invoices_amount');

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $salesAmountByToday = MiniOrderReport::query()->when($startDate, function ($query) use ($startDate) {
            $query->where('created_at', '>', $startDate);
            })->when($endDate, function ($query) use ($endDate) {
                $query->where('created_at', '<', $endDate);
            })->selectRaw('SUM(mini_order_items_count) AS quantity, SUM(total) AS amount, SUM(discount_amount) AS discount_amount')->first();

        $orderCountToday = $order->whereNull('parent_id')->whereBetween('created_at',
            [Carbon::createFromTimestamp(verta()->startDay()->getTimestamp()),
                Carbon::now()
            ])
            ->count();
        $activityLogs = Activity::query()
            ->select('id', 'causer_id', 'description', 'created_at')
            ->latest('id')
            ->take(7)
            ->get();
        $last_logins = $this->reportService->getLastLogins();
        $comments = ProductComment::query()->latest('id')->with(['creator', 'product'])
            ->take(5)->get();
        $newProductCommentsCount = ProductComment::query()->latest('id')
            ->whereStatus(ProductComment::STATUS_PENDING)->count();
        $blogComments = Comment::query()->latest('id')->whereStatus(Comment::STATUS_UNAPPROVED)->with('commentable')->take(5)->get();
        $newBlogCommentsCount = Comment::query()
            ->whereStatus(Comment::STATUS_UNAPPROVED)->count();
        $gender_statistics = $this->reportService->getCustomersGender();
        $dataGender = [
            'labels' => ['مرد', 'زن', 'انتخاب نشده'],
            'data' => [
                $gender_statistics['males_count'],
                $gender_statistics['females_count'],
                $gender_statistics['unknowns_count'],
            ],
        ];
        $sumDataGender = $gender_statistics['males_count'] + $gender_statistics['females_count'] + $gender_statistics['unknowns_count'];
        $siteviews = SiteView::query()
        ->orderBy('id','DESC')
        ->where('date', '>=', now()->subDays(10)->endOfDay())
        ->get()->groupBy('date');

        $siteviewslist = array();

        foreach ($siteviews as $y => $siteview) {
            $siteviewslist[$y] = 0;
            foreach ($siteview as $x) {
                $siteviewslist[$y] = $siteviewslist[$y] + $x->count;
            }
        }
        return view('admin::admin.dashboard',compact(
            'ordersCount',
            'sumDataGender',
            'activityLogs',
            'siteviewslist',
            'salesAmountByToday',
            'dataGender',
            'last_logins',
            'orderCountToday',
            'totalSalesToday',
            'comments',
            'newProductCommentsCount',
            'blogComments',
            'newBlogCommentsCount'
        ));
    }
}
