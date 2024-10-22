<?php

namespace Modules\Report\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bavix\Wallet\Models\Wallet;
use DateTime;
use Hekmatinasser\Verta\Verta;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Area\Entities\Province;
use Modules\Core\Classes\Transaction;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\Withdraw;
use Modules\Dashboard\Services\ReportService;
use Modules\Invoice\Entities\Invoice;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderItem;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Report\Entities\MiniOrderItemReport;
use Modules\Report\Entities\OrderReport;
use Modules\Report\Entities\ProductReport;
use Modules\Report\Entities\StoreReport;
use Modules\Report\Entities\VarietyReport;
use Modules\Report\Exports\CustomerReportExport;
use Modules\Report\Exports\ProductReportExport;
use Modules\Report\Exports\VarietyReportExport;
use PhpParser\Node\Expr\Cast\Object_;
//use Shetabit\Shopit\Modules\Report\Http\Controllers\Admin\ReportController as BaseReportController;
use Illuminate\Database\Eloquent\Builder;
use Shetabit\Shopit\Modules\Report\Services\ChartService;

class ReportController extends Controller
{
    public function wallets()
    {

        $customer_id = request('customer_id', false);
        $startDate = request('start_date', false);
        $endDate = request('end_date', false);
        $reports = Customer::query()
            ->leftjoin('wallets', 'customers.id', '=', 'wallets.holder_id')
            ->leftjoin('transactions as tr', 'wallets.id', '=', 'tr.wallet_id')
            ->addSelect(['customers.id', 'customers.first_name', 'customers.last_name', 'customers.mobile',
                'wallet_balance' => DB::raw("wallets.balance as wallet_balance"),
                'wallet_gift_balance' => DB::raw("wallets.gift_balance as wallet_gift_balance"),
                'total_deposit' => DB::raw("SUM(IF(tr.type = 'deposit' , amount, 0)) as total_deposit"),
                'total_withdraw' => DB::raw("ABS(SUM(IF(tr.type = 'withdraw', amount, 0))) as total_withdraw"),
                'count_deposit' => DB::raw("sum(IF(tr.type = 'deposit' , 1, 0)) as deposit_count"),
                'count_withdraw' => DB::raw("sum(IF(tr.type = 'withdraw', 1, 0)) as withdraw_count"),
            ])
            ->where('wallets.holder_type', Customer::class)
            ->when($customer_id, fn($q) => $q->where(DB::raw('customers.id'), $customer_id))
            ->when($startDate, fn($q) => $q->where('tr.created_at', '>', $startDate))
            ->when($endDate, fn($q) => $q->where('tr.created_at', '<', $endDate))
            ->with('addresses')
            ->groupBy('id')->sortFilter();


        $sum_balance = 0;
        foreach ($reports->get() as $report) {
            $sum_balance += $report->wallet_balance;
        }

        $sum_gift_balance =0;

        foreach ($reports->get() as $report) {
            $sum_gift_balance += $report->wallet_gift_balance;
        }

        $reports = $reports
            ->paginate(20   );
        foreach ($reports as $report) {
            $report->makeHidden('wallet');
        }


//        return response()->success('', compact('reports', 'sum_balance','sum_gift_balance'));
        return  $this->walletsIndex($reports);
    }

    public function walletsIndex($reports = null)
    {

        return view('report::admin.wallet',compact('reports'));
    }

    public function customers(Request $request)
    {
        $reports = ($this->reportService->customerReport($request))->groupBy('customer_id')->addSelect(DB::raw('Max(created_at) AS latest_order_date')); // IMPORTANT
        $reports = $reports->take(10000)->paginate(\request('per_page', 10));

        if (\request()->header('accept') == 'x-xlsx') {
            foreach ($reports as $report) {
                $report->last_order_date = verta($report->created_at)->format('Y-m-d');
                $report->last_order_mounth = verta($report->created_at)->formatWord('F');
                $report->last_factor_number = $report->id;
                $address = json_decode($report->address);
                $report->real_full_name = ($address->first_name ?? '').' '.($address->last_name ?? '');
                $report->append(['count']);
            }
            return Excel::download(new CustomerReportExport($reports),
                __FUNCTION__ . '-' . now()->toDateString() . '.xlsx');
        }

        foreach ($reports as $report) {
            $latestOrder = $report->customer->orders[0]->whereIn('status',Order::ACTIVE_STATUSES)->latest()->first();
            $report->order_items_count = $report->_order_items_count;
            $report->attribute_ids = $report->_attribute_ids;
            $report->product_ids = $report->_product_ids;
            $report->last_order_date = verta($latestOrder->created_at)->format('Y-m-d');
            $report->last_factor_number = $latestOrder->id;
            $lastAddress = $latestOrder->address()->first();
            $report->last_user_fullname_from_address = ($lastAddress->first_name ?? '').' '.($lastAddress->last_name ?? '') ;
            $report->append(['statuses_info', 'count', 'order_info']);
        }
        $incomeDitail = $this->customersIncomesDetail($request);
        return $this->customerIndex($reports);
    }

    public function customerIndex($reports = [])
    {
        $provinces = Province::query()->where('status',1)->select('id','name')->with('cities')->get();
        $max_invoice_amount = Order::max('total_invoices_amount');
        $max_item_count = Order::max('total_items_count');
        return view('report::admin.customer-filter',compact(['provinces','max_invoice_amount','max_item_count','reports']));
    }

    public function customersIncomesDetail(Request $request)
    {
        $report = $this->reportService->customerReport($request);
        $total_income = (clone $report)->sum('total');
        $total_order_items_count = (clone $report)->sum('order_items_count');
        $total_discount_amount = (clone $report)->sum('discount_amount');
        $total_not_coupon_discount_amount = (clone $report)->sum('not_coupon_discount_amount');
        $total_shipping_amount = (clone $report)->sum('shipping_amount');
        $sum_gift_wallet_amount = (clone $report)->sum('gift_wallet_amount');

        $order_statuses = Order::getAllStatuses(clone $report);

        return response()->success('', compact(
            'total_income', 'total_order_items_count', 'total_discount_amount',
            'total_not_coupon_discount_amount',
            'total_shipping_amount', 'order_statuses','sum_gift_wallet_amount',
        ));

    }






    // حالت های موردنیاز جهت محاسبه
    function getStatusesForReport(): array
    {
        return ['new','delivered', 'in_progress'];
    }

    // تعداد سفارش‌ها
    public function getTotalOrders($date=null,$request=null){

        // select orders.status, sum(quantity) from order_items INNER JOIN orders on orders.id = order_items.order_id group by orders.status;

        $totals = DB::table('orders')
            ->select('status',DB::raw('count(id) as count'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

//        $sql = $totals->toSql();
//        $bindings = $totals->getBindings();
//        foreach ($bindings as $binding) {
//            $pos = strpos($sql, '?');
//            $sql = substr_replace($sql, $binding, $pos, 1);
//        }
//
//        Log::info('Query is : ' . $sql);

        $totals = $totals->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // تعداد اقلام
    public function getTotalOrderItems($date=null,$request=null){
        $totals = DB::table('orders')
            ->select('status',DB::raw('sum(items_quantity) as total'))
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // مجموع فروش (درآمد)
    public function getTotalIncome($date=null,$request=null){
        $totalAmount = $this->getTotalAmount($date,$request);
        $totalDiscountAmount = $this->getTotalDiscountAmount($date,$request);
        return $totalAmount - $totalDiscountAmount;
    }

    // مجموع هزینه کالا
    public function getTotalAmount($date=null,$request=null){

        // SELECT orders.status, sum(order_items.quantity*order_items.amount) + orders.shipping_amount - orders.discount_amount as total FROM `orders` join order_items on order_items.order_id = orders.id GROUP by orders.status;

        $totals = DB::table('orders')
//            ->join('order_items as oi','oi.order_id','=','id')
//            ->select('status',DB::raw('sum(oi.quantity*oi.amount) as total'))
            ->select('status',DB::raw('sum(total_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // مجموع فاکتورها
    public function getTotalInvoiceAmount($date=null,$request=null){

        $totals = DB::table('orders as o')
            ->join('invoices as i','o.id','=','i.payable_id')
//            ->select('status',DB::raw('sum(oi.quantity*oi.amount) as total'))
            ->select('o.status',DB::raw('sum(i.amount) as total'))
            ->whereIn('o.status',$this->getStatusesForReport());
//            ->whereNull('o.parent_id');

        if ($date){
            $totals = $totals->whereDate('o.created_at',$date);
        }

        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request,true);
        }

        $totals = $totals->groupBy('o.status')
            ->pluck('total', 'o.status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // مجموع فاکتورها
    public function getTotalPayableAmount($date=null,$request=null){

        $totals = DB::table('orders')
//            ->join('invoices as i','o.id','=','i.payable_id')
//            ->select('status',DB::raw('sum(oi.quantity*oi.amount) as total'))
            ->select('status',DB::raw('sum(total_payable_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }

        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // مجموع تخفیف
    public function getTotalDiscountAmount($date=null,$request=null){
        $discountWithCoupon = $this->getTotalDiscountAmountWithCoupon($date,$request);
        $discountWithoutCoupon = $this->getTotalDiscountAmountWithoutCoupon($date,$request);
        return $discountWithCoupon + $discountWithoutCoupon;
    }

    // تخفیف با کوپن
    public function getTotalDiscountAmountWithCoupon($date=null,$request=null){

        // SELECT orders.status, sum(order_items.quantity*order_items.amount) + orders.shipping_amount - orders.discount_amount as total FROM `orders` join order_items on order_items.order_id = orders.id GROUP by orders.status;

        $totals = DB::table('orders')
//            ->join('order_items as oi','oi.order_id','=','id')
            ->select('status',DB::raw('sum(discount_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNotNull('coupon_id')
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // تخفیف بدون کوپن
    public function getTotalDiscountAmountWithoutCoupon($date=null,$request=null){

        // SELECT orders.status, sum(order_items.quantity*order_items.amount) + orders.shipping_amount - orders.discount_amount as total FROM `orders` join order_items on order_items.order_id = orders.id GROUP by orders.status;

        $totals = DB::table('orders')
//            ->join('order_items as oi','oi.order_id','=','id')
            ->select('status',DB::raw('sum(discount_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('coupon_id')
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // مجموع هزینه ارسال
    public function getTotalShippingAmount($date=null,$request=null){

        // select status, sum(shipping_amount) as total from orders where status in ('delivered', 'in_progress') and parent_id is null and date(`o`.`created_at`) = '2023-07-10' group by `o`.`status`

        $totals = DB::table('orders')
//            ->join('order_items as oi','oi.order_id','=','id')
            ->select('status',DB::raw('sum(shipping_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }

    // پرداخت از طریق درگاه
    public function getTotalPaidFromGateway($date=null,$request=null){
        $totals = DB::table('orders')
            ->select('status',DB::raw('sum(total_payable_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }


    // خرید با اعتبار هدیه
    public function getTotalGiftWalletAmount($date=null,$request=null){

        // select status, sum(shipping_amount) as total from orders where status in ('delivered', 'in_progress') and parent_id is null and date(`o`.`created_at`) = '2023-07-10' group by `o`.`status`

        $totals = DB::table('orders')
//            ->join('order_items as oi','oi.order_id','=','id')
            ->select('status',DB::raw('sum(gift_wallet_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }


    // خرید با اعتبار کیف پول
    public function getTotalPaidFromWallet($date=null,$request=null){
        $totals = DB::table('orders')
            ->select('status',DB::raw('sum(used_wallet_amount) as total'))
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            $totals = $this->applyFiltersToTotal($totals,$request);
        }

        $totals = $totals->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sum = 0;
        $statuses_to_sum = $this->getStatusesForReport();
        foreach ($statuses_to_sum as $key) {
            $sum += $totals[$key]??0;
        }

        return $sum;
    }


    // شارژ کیف پول
    public function getTotalWalletDeposit($date=null,$request=null){
//        $totals = DB::table('invoices')
//            ->select(DB::raw('sum(amount) as total'))
//            ->where('status',"success")
//            ->where('payable_type',"Modules\Customer\Entities\Deposit");

        $totals = DB::table('deposits')
            ->select(DB::raw('sum(amount) as total'))
            ->where('status',"success");

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            if ($request->start_date){
                $totals = $totals->where('created_at','>=',$request->start_date);
            }
            if ($request->end_date){
                $totals = $totals->where('created_at','<=',$request->end_date);
            }
        }

        return (int)$totals->first()->total;
    }


    // شارژ کیف پول توسط ادمین
    public function getTotalWalletDepositByAdmin($date=null,$request=null,$charge_type=5){

        $totals = DB::table('transactions')
            ->select(DB::raw('sum(amount) as total'))
            ->where('type',"deposit")
            ->where('confirmed',1)
            ->where('charge_type_id',$charge_type)
            ->where('payable_type','Modules\Customer\Entities\Customer');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            if ($request->start_date){
                $totals = $totals->where('created_at','>=',$request->start_date);
            }
            if ($request->end_date){
                $totals = $totals->where('created_at','<=',$request->end_date);
            }
        }

        return (int)$totals->first()->total;
    }


    // کاهش کیف پول (ادمین + خرید)
    public function getTotalWalletWithdraw($date=null,$request=null){

        $totals = DB::table('transactions')
            ->select(DB::raw('sum(amount) as total'))
            ->where('type',"withdraw")
            ->where('confirmed',1)
            ->where('payable_type','Modules\Customer\Entities\Customer')
            ->whereNotNull('meta');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            if ($request->start_date){
                $totals = $totals->where('created_at','>=',$request->start_date);
            }
            if ($request->end_date){
                $totals = $totals->where('created_at','<=',$request->end_date);
            }
        }

        return (int)$totals->first()->total;
    }


    // موجودی کیف پول
    public function getTotalWallet($date=null, $request=null){
        $totals = DB::table('transactions')
            ->select('type',DB::raw('sum(amount) as total'))
            ->where('confirmed',1)
            ->where('payable_type',"Modules\Customer\Entities\Customer")
            ->groupBy('type');

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            if ($request->start_date){
                $totals = $totals->where('created_at','>=',$request->start_date);
            }
            if ($request->end_date){
                $totals = $totals->where('created_at','<=',$request->end_date);
            }
        }

        $result = array();
        foreach ($totals->get()->toArray() as $item) {
            $result[$item->type] = $item->total;
        }
        $deposit = array_key_exists('deposit',$result)?$result['deposit']:0;
        $withdraw = array_key_exists('withdraw',$result)?$result['withdraw']:0;
        return $deposit + $withdraw;
    }

    public function getTotalGiftWallet($date=null, $request=null){
        $totals = DB::table('wallets')
            ->where('holder_type',"Modules\Customer\Entities\Customer")
            ->sum('gift_balance');
        return $totals;
    }

    // مجموع تراکنش های ورودی
    public function getTotalTransaction($date=null, $request=null, $type='deposit', $chargeType=null){
        $totals = DB::table('transactions')
            ->select(DB::raw('sum(amount) as total'))
            ->where('confirmed',1)
            ->where('payable_type',"Modules\Customer\Entities\Customer")
            ->when($chargeType == 'charge', function ($query) {
                $query->whereNull('meta'); // افزایش هایی که شارژ هستند
            })
            ->when($chargeType == 'not_charge', function ($query) {
                $query->whereNotNull('meta'); // افزایش هایی که شارژ نیستند
            })
            ->where('type',$type);

        if ($date){
            $totals = $totals->whereDate('created_at',$date);
        }
        if ($request && $request->all()){
            if ($request->start_date){
                $totals = $totals->where('created_at','>=',$request->start_date);
            }
            if ($request->end_date){
                $totals = $totals->where('created_at','<=',$request->end_date);
            }
        }

        return $totals->value('total');

//        $result = array();
//        foreach ($totals->get()->toArray() as $item) {
//            $result[$item->type] = $item->total;
//        }
//        return $result['deposit'] + $result['withdraw'];
    }


    public function getPaidWithdraws($date)
    {
        $withdraws = Withdraw::query()
            ->whereDate('created_at',$date)
            ->where('status','paid')
            ->select('amount')
            ->sum('amount');
        return $withdraws;
    }


    public function applyFiltersToTotal($totals,$request,$use_join=false){
        $start_date = str_replace("+"," ",$request->start_date);
        $end_date = str_replace("+"," ",$request->end_date);
        $total_lower = $request->total_lower;
        $total_higher = $request->total_higher;
        $items_count_lower = $request->items_count_lower;
        $items_count_higher = $request->items_count_higher;
//        $province_id = $request->province_id;
//        $city_id = $request->city_id;
//        $category_ids = $request->category_ids;
        $customer_id = $request->customer_id;

        if ($start_date){
            $field = $use_join?'o.created_at':'created_at';
            $totals = $totals->where($field,'>=',$start_date);
        }
        if ($end_date){
            $field = $use_join?'o.created_at':'created_at';
            $d = explode(" ",$end_date);
            $d[1] .= ":59";
            $end_date = implode(" ",$d);
            $totals = $totals->where($field,'<=',$end_date);
        }
        if ($total_lower){
            $totals = $totals->where('total_amount','>=',$total_lower);
        }
        if ($total_higher){
            $totals = $totals->where('total_amount','<=',$total_higher);
        }
        if ($items_count_lower){
            $totals = $totals->where('items_quantity','>=',$items_count_lower);
        }
        if ($items_count_higher){
            $totals = $totals->where('items_quantity','<=',$items_count_higher);
        }
        if ($customer_id){
            $totals = $totals->where('customer_id',$customer_id);
        }

        return $totals;
    }


    protected $result = [];
    public function getTotalReport()
    {
        $request = \Request();
        $this->result = [
            'total_amount' => 0,
            'total_invoice_amount' => 0,
            'total_shipping_amount' => 0,
            'total_orders' => 0,
            'total_order_items' => 0,
            'total_discount_amount' => 0,
            'total_discount_with_coupon' => 0,
            'total_discount_without_coupon' => 0,
            'total_gift_wallet_amount' => 0,
            'total_paid_from_wallet' => 0,
            'total_paid_from_gateway' => 0,
            'total_paid' => 0,
            'total_wallet_deposit' => 0,
            'total_wallet' => 0,
            'total_gift_wallet' => 0,
            'total_main_wallet' => 0,
            'total_deposit' => 0,
            'total_deposit_charge' => 0,
            'total_deposit_not_charge' => 0,
            'total_withdraw' => 0,
        ];

//        if (\Request()->has('newFeature') && \Request()->newFeature) {
//
//        }


        DB::table('orders')
//            ->whereIn('id', [145677])
            ->whereIn('status', Order::ACTIVE_STATUSES)
            ->whereNull('parent_id')
//            ->select(['id','customer_id','created_at','address_id','shipping_amount','discount_amount','status','parent_id'])
            ->select(['id','parent_id','created_at','status', 'address_id','customer_id','discount_amount','status',
                'children_count',
                'total_quantity',
                'total_items_count',
                'discount_on_products',
                'total_products_prices_with_discount',
                'total_shipping_amount',
                'paid_by_wallet_gift_balance',
                'paid_by_wallet_main_balance',
                'total_invoices_amount'
            ])
            ->when($request->filled('start_date'),function($q) use ($request) {
                $q->where('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'),function($q) use ($request) {
                $q->where('created_at', '<=', $request->end_date);
            })
            ->when($request->filled('customer_id'),function($q) use ($request) {
                $q->where('customer_id',$request->customer_id);
            })
            ->orderBy('id', 'desc')
            ->chunk(100, function ($orders) {
                foreach ($orders as $pureOrder) {
                    $parent_children_ids = [$pureOrder->id];

                    $children_count = $pureOrder->children_count;
                    $total_quantity = $pureOrder->total_quantity;
                    $items_count = $pureOrder->total_items_count;
                    $discount_on_products = $pureOrder->discount_on_products;
                    $discount_on_order = $pureOrder->discount_amount;
                    $total_products_prices_with_discount = $pureOrder->total_products_prices_with_discount;
                    $total_products_prices_without_discount = $pureOrder->total_products_prices_with_discount + $pureOrder->discount_on_products;
                    $shipping_amount = $pureOrder->total_shipping_amount;
                    $paid_by_wallet_gift_balance = $pureOrder->paid_by_wallet_gift_balance;
                    $paid_by_wallet_main_balance = $pureOrder->paid_by_wallet_main_balance;
                    $paid_by_wallet = $pureOrder->paid_by_wallet_gift_balance + $pureOrder->paid_by_wallet_main_balance;
                    $total_invoice_amount = $pureOrder->total_invoices_amount;

                    // children
//                    $children = DB::table('orders')
//                        ->whereIn('status', Order::ACTIVE_STATUSES)
//                        ->select(['id','customer_id','created_at','address_id','shipping_amount','discount_amount','status','parent_id'])
//                        ->where('parent_id', $pureOrder->id)
//                        ->get();
//                    if ($children) {
//                        foreach ($children as $child) {
//                            $parent_children_ids[] = $child->id;
//                            $children_count += 1;
//                            $shipping_amount += $child->shipping_amount;
//                            $discount_on_order += $child->discount_amount;
//                        }
//                    }
//                    // invoices
//                    $invoices = DB::table('invoices')
//                        ->select(['id','amount','wallet_amount','gift_wallet_amount','payable_id','payable_type','status'])
//                        ->where('status', Invoice::STATUS_SUCCESS)->where('payable_type', 'Modules\\Order\\Entities\\Order')->whereIn('payable_id', $parent_children_ids)->get();
//                    foreach ($invoices as $invoice) {
//                        $total_invoice_amount += $invoice->amount;
//                        $paid_by_wallet += $invoice->wallet_amount;
//                        $paid_by_wallet_main_balance += ($invoice->wallet_amount - $invoice->gift_wallet_amount);
//                        $paid_by_wallet_gift_balance += $invoice->gift_wallet_amount;
//                    }
//
//                    // all items
//                    $order_items = DB::table('order_items')
//                        ->select(['id','amount','order_id','quantity','discount_amount','status'])
//                        ->where('status', 1)->whereIn('order_id', $parent_children_ids)->get();
//                    foreach ($order_items as $item) {
//                        $total_quantity += $item->quantity;
//                        $items_count += 1;
//                        $discount_on_products += ($item->discount_amount * $item->quantity);
//                        $total_products_prices_with_discount += ($item->amount * $item->quantity);
//                        $total_products_prices_without_discount += (($item->amount + $item->discount_amount) * $item->quantity);
//                    }

                    $this->result['total_amount'] += $total_products_prices_without_discount; /* میزان فروش ناخالص (بدون تخفیف، بدون حمل و نقل) */
                    $this->result['total_invoice_amount'] += ($total_products_prices_with_discount + $shipping_amount - $discount_on_order); /* مبلغ کل فاکتور (فروش ناخالص با حمل و نقل و تخفیف */
                    $this->result['total_order_items'] += ($total_quantity); /*تعداد آیتم های سفارشات*/
                    $this->result['total_orders'] += 1; /*تعداد سفارشات*/ /*دقت کنید در اینجا فرزندان این سفارش را به عنوان سفارش نمی شماریم*/
                    $this->result['total_shipping_amount'] += ($shipping_amount); /*مجموع هزینه های حمل و نقل*/
                    $this->result['total_gift_wallet_amount'] += ($paid_by_wallet_gift_balance); /* خرید کاربران با موجودی هدیه */
                    $this->result['total_paid_from_wallet'] += ($paid_by_wallet_main_balance); /*پرداخت شده از موجودی اصلی کیف پول*/
                    $this->result['total_paid_from_gateway'] += ($total_invoice_amount - $paid_by_wallet); /*تسویه از درگاه*/ /*در واقع پرداخت شده از درگاه هستش*/
                    $this->result['total_discount_with_coupon'] += $discount_on_order; /*تخفیف با کد. تخفیفی که روی کوپن هست رو میده*/
                    $this->result['total_discount_without_coupon'] += $discount_on_products; /*تخفیف بدون کد. تخفیفی که روی محصولات هست رو میده*/
                    $this->result['total_discount_amount'] += ($discount_on_products + $discount_on_order); /*مجموع تخفیف*/
                    $this->result['total_paid'] += $total_invoice_amount; /*مجموع تسویه ها*/ /*مجموع مبالغ موجود روی فاکتور ها هستش*/
                }
            });


        $this->result['total_wallet_deposit'] = (int)DB::table('deposits')
            ->select(DB::raw('sum(amount) as total'))
            ->where('status',"success")
            ->when($request->filled('start_date'),function($q) use ($request) {
                $q->where('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'),function($q) use ($request) {
                $q->where('created_at', '<=', $request->end_date);
            })
            ->when($request->filled('customer_id'),function($q) use ($request) {
                $q->where('customer_id',$request->customer_id);
            })
            ->first()->total;

        $wallet_details = DB::table('wallets')
            ->select(DB::raw('sum(balance) as sum_balance, sum(gift_balance) as sum_gift_balance'))
            ->when($request->filled('customer_id'),function($q) use ($request) {
                $q->where('holder_id',$request->customer_id)->where('holder_type', 'Modules\\Customer\\Entities\\Customer');
            })
            ->first();

        $this->result['total_wallet'] = $wallet_details->sum_balance;
        $this->result['total_gift_wallet'] = (string)$wallet_details->sum_gift_balance;
        $this->result['total_main_wallet'] = ($wallet_details->sum_balance - $wallet_details->sum_gift_balance) * -1;


        return response()->json($this->result);










        // ============================================================== Ride
    // در صورتی که گزارش درخواست شده مربوط به کمتر از یک روز باشد داده های محاسبه شده را حذف نموده و دوباره محاسبه می کند
        if (\Request()->start_date && \Request()->end_date){
            $date_seconds = 24*60*60;
            if (strtotime(\Request()->end_date) - strtotime(\Request()->start_date) < $date_seconds){

                DB::table('orders')
                    ->whereBetween('created_at',[\Request()->start_date,\Request()->end_date])
                    ->update(['items_count' => null]);
            }
        }

        (new \Modules\Core\Helpers\Helpers)->updateOrdersUsefulData();
        (new \Modules\Core\Helpers\Helpers)->updateOrdersCalculateData();
        (new \Modules\Core\Helpers\Helpers)->updateChargeTypeOfTransactions();

        $total_amount = $this->getTotalAmount(null,\Request());
        $total_invoice_amount = $this->getTotalInvoiceAmount(null,\Request());
        $total_payable_amount = $this->getTotalPayableAmount(null,\Request());
        $total_income = $this->getTotalIncome(null,\Request());
        $total_shipping_amount = $this->getTotalShippingAmount(null,\Request());
        $total_orders = $this->getTotalOrders(null,\Request());
        $total_order_items = $this->getTotalOrderItems(null,\Request());
        $total_discount_amount = $this->getTotalDiscountAmount(null,\Request());
        $total_discount_with_coupon = $this->getTotalDiscountAmountWithCoupon(null,\Request());
        $total_discount_without_coupon = $this->getTotalDiscountAmountWithoutCoupon(null,\Request());
        $total_gift_wallet_amount = $this->getTotalGiftWalletAmount(null,\Request());
        $total_paid_from_wallet = $this->getTotalPaidFromWallet(null,\Request());
        $total_paid_from_gateway = $this->getTotalPaidFromGateway(null,\Request());
        $total_wallet_deposit = $this->getTotalWalletDeposit(null,\Request());
        $total_wallet = $this->getTotalWallet(null,\Request());
        $total_gift_wallet = $this->getTotalGiftWallet(null,\Request());
        $total_deposit = $this->getTotalTransaction(null,\Request(),'deposit',null);
        $total_deposit_charge = $this->getTotalTransaction(null,\Request(),'deposit','charge');
        $total_deposit_not_charge = $this->getTotalTransaction(null,\Request(),'deposit','not_charge');
        $total_withdraw = $this->getTotalTransaction(null,\Request(),'withdraw',null);

        $result = [
            'total_amount' => $total_amount,
            'total_invoice_amount' => $total_amount + $total_shipping_amount - $total_discount_amount,  //$total_invoice_amount,
//            'total_payable_amount' => $total_payable_amount,
//            'total_income' => $total_income,
            'total_shipping_amount' => $total_shipping_amount,
            'total_orders' => $total_orders,
            'total_order_items' => $total_order_items,
            'total_discount_amount' => $total_discount_amount,
            'total_discount_with_coupon' => $total_discount_with_coupon,
            'total_discount_without_coupon' => $total_discount_without_coupon,
            'total_gift_wallet_amount' => $total_gift_wallet_amount,
            'total_paid_from_wallet' => $total_paid_from_wallet,
            'total_paid_from_gateway' => $total_paid_from_gateway,
            'total_paid' => $total_paid_from_gateway + $total_paid_from_wallet + $total_gift_wallet_amount,
            'total_wallet_deposit' => $total_wallet_deposit,
            'total_wallet' => $total_wallet,
            'total_gift_wallet' => $total_gift_wallet,
            'total_main_wallet' => $total_wallet - $total_gift_wallet,
            'total_deposit' => $total_deposit,
            'total_deposit_charge' => $total_deposit_charge,
            'total_deposit_not_charge' => $total_deposit_not_charge,
            'total_withdraw' => $total_withdraw,
        ];
        return response()->json($result);
    }

    public function getTotalReportList(){
        $request = \Request();

        $page = $request->page;
        $per_page = $request->per_page;

        $list = DB::table('orders')
            ->select(
                'id',
                'customer_id',
                DB::raw('count(*) as orders_count'),
                DB::raw('sum(items_quantity) as items_count'),
                DB::raw('sum(total_amount) as total_order_amount'),
            )
//            ->where('shipping_amount','<>','0')
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id')
            ->latest('created_at')
            ->groupBy('customer_id');

        if ($request && $request->all()){
            $list = $this->applyFiltersToTotal($list,$request);
        }


        if (\request()->header('accept') == 'x-xlsx') {
            $list = $list->get();
            foreach($list as $item){
                // Append additional data to each item
                $customer = DB::table('customers')->select(['id','mobile','first_name','last_name'])->where('id',$item->customer_id)->first();
                $item->mobile = $customer->mobile;
                //            $item->orders_count = $this->getCustomerOrderCount($item->customer_id);
                $last_customer_order = DB::table('orders')
                    ->select(['id','created_at','first_name','last_name','total_amount'])
                    ->whereIn('status',$this->getStatusesForReport())
                    ->whereNull('parent_id')
                    ->where('customer_id',$item->customer_id)
                    //                ->where('customer_id','11081')
                    ->latest('created_at')->first();

                $last_date = (new DateTime($last_customer_order->created_at))->format('Y-m-d');
                $item->last_order_code = $last_customer_order->id;
                $item->last_order_date = (new Helpers)->convertMiladiToShamsi($last_date);
                $item->last_order_fee = Helpers::toDigit($last_customer_order->total_amount);
                $item->last_order_month = (new Helpers)->convertMiladiToShamsi($last_date,'F');
                $item->last_order_year = (new Helpers)->convertMiladiToShamsi($last_date,'Y');

                if (strlen($customer->first_name.$customer->last_name)>2){
                    $item->full_name = $customer->first_name . " " . $customer->last_name;
                } else {
                    $item->full_name = $last_customer_order->first_name . " " . $last_customer_order->last_name;;
                }

                $item->total_order_amount = Helpers::toDigit($item->total_order_amount);

                $item->gift_wallet_amount = DB::table('wallets')->where('holder_type','Modules\Customer\Entities\Customer')->where('holder_id',$item->customer_id)->value('gift_balance')??0;
            }
            return Excel::download(new CustomerReportExport($list),
                __FUNCTION__.'-' . now()->toDateString() . '.xlsx');

        } else {
            $list = DB::table('orders')
                ->select(
                    'id',
                    'customer_id',
                    DB::raw('count(*) as orders_count'),
                    DB::raw('sum(items_quantity) as items_count'),
                    DB::raw('sum(total_amount) as total_order_amount'),
                )
//            ->where('shipping_amount','<>','0')
                ->whereIn('status',$this->getStatusesForReport())
                ->whereNull('parent_id')
                ->latest('created_at')
                ->groupBy('customer_id');

            if ($request && $request->all()){
                $list = $this->applyFiltersToTotal($list,$request);
            }

            $list = $list->paginate($per_page);

            $list->getCollection()->map(function($item) {
                // Append additional data to each item
                $customer = DB::table('customers')->where('id',$item->customer_id)->first();
                $item->mobile = $customer->mobile;

                //            $item->orders_count = $this->getCustomerOrderCount($item->customer_id);
                $last_customer_order = DB::table('orders')
                    ->whereIn('status',$this->getStatusesForReport())
                    ->whereNull('parent_id')
                    ->where('customer_id',$item->customer_id)
                    //                ->where('customer_id','11081')
                    ->latest('created_at')->first();

                $last_date = (new DateTime($last_customer_order->created_at))->format('Y-m-d');
                $item->last_order_code = $last_customer_order->id;
                $item->last_order_date = (new Helpers)->convertMiladiToShamsi($last_date);
                $item->last_order_fee = Helpers::toDigit($last_customer_order->total_amount);
                $item->last_order_month = (new Helpers)->convertMiladiToShamsi($last_date,'F');
                $item->last_order_year = (new Helpers)->convertMiladiToShamsi($last_date,'Y');

                if (strlen($customer->first_name.$customer->last_name)>2){
                    $item->full_name = $customer->first_name . " " . $customer->last_name;
                } else {
                    $item->full_name = $last_customer_order->first_name . " " . $last_customer_order->last_name;;
                }

                $item->total_order_amount = Helpers::toDigit($item->total_order_amount);

                $item->gift_wallet_amount = DB::table('wallets')->where('holder_type','Modules\Customer\Entities\Customer')->where('holder_id',$item->customer_id)->value('gift_balance')??0;

                return $item;
            });
        }
        return response()->success('Get Filtered orders list :)', compact('list'));
    }


    public function getTransactionDeposit(){
        $report = DB::table('transactions as t')
            ->join('charge_types as ct','ct.id','=','t.charge_type_id')
            ->select(
//                DB::raw("CAST(json_unquote(JSON_EXTRACT(meta, '$.description')) as CHAR) as description"),
                DB::raw("sum(amount) as total"),
                'ct.title as description'
            )
            ->where('confirmed',1)
            ->where('payable_type',"Modules\Customer\Entities\Customer")
//            ->whereNotNull('meta') // افزایش هایی که شارژ نیستند
            ->where('type','deposit')
//            ->groupBy('description');
            ->groupBy('charge_type_id');

//        if ($date){
//            $report = $report->whereDate('created_at',$date);
//        }
        if (request() && request()->all()){
            if (request()->start_date){
                $report = $report->where('t.created_at','>=',request()->start_date);
            }
            if (request()->end_date){
                $report = $report->where('t.created_at','<=',request()->end_date);
            }
        }

        $report = $report->paginate(100);
        return response()->success('تراکنش های ورودی به تفکیک شرح تراکنش',compact('report'));
    }

    public function getCustomerOrderCount($customer_id): int
    {
         return DB::table('orders')
             ->where('customer_id',$customer_id)
             ->whereIn('status',$this->getStatusesForReport())
             ->whereNull('parent_id')
             ->count();
    }
    public function getCustomerItemsCount($customer_id): int
    {
         return DB::table('orders')
             ->where('customer_id',$customer_id)
             ->select('items_quantity')
             ->whereIn('status',$this->getStatusesForReport())
             ->whereNull('parent_id')
             ->sum('items_quantity');
    }


    // آمار نموداری
    public function chartType1Light(){

        $type = \Request()['type'];
//        $mode = \Request()['mode'];
        $offset_year = \Request()['offset_year'];
        $month = \Request()['month'];

        $thisYear = (new Helpers)->getThisYearPersian();
        $year = $thisYear - $offset_year;

        switch ($type){
            case 'week':
                $result = $this->getWeeklyReport();
                break;

            case 'month':
                $result = $this->getMonthlyReport($year,$month);
                break;

            case 'year':
                $result = $this->getYearlyReport($year);
                break;

            default: $result = "پارامتر نامعتبر";
        }

        dd($result);
        return response()->json($result);
    }

    public function getWeeklyReport(){
        $firstDay = (new Helpers)->firstDayOfWeek();
        return $this->getRangedReport($firstDay,7);
    }

    public function getMonthlyReport($year,$month){
        $firstDay = (new Helpers)->convertShamsiToMiladi("$year/$month/01");
        $length = (new Helpers)->getDaysOfMonth($year,$month);
        return $this->getRangedReport($firstDay,$length);
    }

    public function getYearlyReport($year){
        $firstDay = (new Helpers)->convertShamsiToMiladi("$year/01/01");
        $length = (new Helpers)->getDaysOfYear($year);
        return $this->getRangedReport($firstDay,$length);
    }

    public function getRangedReport($firstDay,$length){
        $result = [];
        for ($i = 0 ; $i <= $length-1 ; $i++){
            $date =  date("Y-m-d", strtotime($firstDay . " + $i days"));
            $result[] = [
                'date' => convertMiladiToShamsiWithoutTime($date),
                'quantity' => $this->getTotalOrders($date),
                'items' => $this->getTotalOrderItems($date),
                'amount' => $this->getTotalAmount($date),
                'income' => $this->getTotalIncome($date),
                'discount_amount' => $this->getTotalDiscountAmount($date),
                'shipping_amount' => $this->getTotalShippingAmount($date),
                'gift_wallet_amount' => $this->getTotalGiftWalletAmount($date),
            ];
        }
        return $result;
    }


    /* لیست تمامی تنوع ها */
    public function varietiesListLight()
    {
        $start = \Request()->start_date;
        $end = \Request()->end_date.":59";
        $orders = DB::table('orders')
            ->select('id')
            ->whereIn('status',['new','delivered','in_progress'])
            ->where('created_at','>=',$start)
            ->where('created_at','<=',$end)
            ->where('parent_id',null)
            ->pluck('id')
            ->toArray();

        $sub_orders = DB::table('orders')
            ->select('id')
            ->whereIn('parent_id',$orders)
            ->whereIn('status',['new','delivered','in_progress'])
            ->pluck('id')
            ->toArray();

        $mergedOrdersArray = array_merge($orders, $sub_orders);

        $report = DB::table('order_items as oi')
            ->join('products as p' ,'oi.product_id' , '=', 'p.id')
            ->leftJoin('varieties as v' ,'oi.variety_id' , '=', 'v.id')
            ->leftJoin('colors as c' ,'v.color_id' , '=', 'c.id')
            ->select(
                "p.id",
                "oi.quantity",
                "oi.amount",
                "oi.discount_amount",
                DB::raw("oi.amount + oi.discount_amount as real_price"),
//                DB::raw("oi.amount * sum(oi.quantity) as total"),
                DB::raw("(oi.amount + oi.discount_amount) * sum(oi.quantity) as total"),
                "p.title",
                DB::raw("json_unquote(JSON_EXTRACT(oi.extra, '$.attributes[0].value')) as value"),
                DB::raw("json_unquote(JSON_EXTRACT(oi.extra, '$.attributes[0].label')) as label"),
                "c.name as color",
                DB::raw("sum(oi.quantity) as sum")
            )
            ->whereIn('order_id', $mergedOrdersArray)
            ->groupBy('oi.variety_id')
            ->where('oi.status', 1)
            ->get();

        $final_report = [];
        foreach ($report as $item) {
//            $data['id'] = $item->id;

            $title = [];
            $title[] = $item->title;
            if($item->label) {$title[] = $item->label;}
            if($item->value) {$title[] = $item->value;}
            if($item->color) {$title[] = "رنگ " . $item->color;}

            $data['id'] = $item->id;
            $data['title'] = implode(" | ", $title);
            $data['sell_quantity'] = $item->sum;
            $data['price'] = $item->real_price;
            $data['total_sale'] = $item->total;

            $final_report[] = $data;
        }

//        $final_report = (Object)$final_report;

        if (\request()->header('accept') == 'x-xlsx') {
            return Excel::download(new VarietyReportExport($final_report),
                __FUNCTION__.'-' . now()->toDateString() . '.xlsx');
        }

        return response()->success('', compact('final_report'));
    }

    public function commonHtmlForReport($date=null)
    {
        $checked = isset($_GET['reCalculate'])?'checked':'';
        $html = "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'>";
        $html .= "<script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>";
        $html .= "<style>
                        @font-face{
                            font-family:iransans;
                            src:url('/assets/font/IRANSansWeb(FaNum).ttf');
                        }
                        body{
                            direction: rtl;
                            font-family: iransans,Tahoma,serif;
                        }
                        .bg-warning{
                            background: #d2b16b;
                        }
                        table,th,td{
                            border: 1px solid black;
                            text-align: center;
                            font-family: iransans,Tahoma,serif;
                        }
                        table{
                            border-collapse: collapse;
                            font-size: 80%;
                        }
                        td,th{
                            padding: 3px 6px !important;
                            vertical-align: middle !important;
                        }
                        .inline-date{
                            display: inline-block;
                            direction: ltr;
                        }
                        .table thead th {
                            border-bottom: 2px solid #000000;
                        }
                        .table td, .table th {
                            border-top: 1px solid #000000;
                        }
                    </style>
                    <script>
                       function showReport(){
                            const btn = document.getElementById('show_report');
                            btn.click();
                       }
                       function nextDay(){
                            // Get the input date value
                            let inputDate = new Date(document.getElementById('inputDate').value);

                            // Add 1 day to the input date
                            inputDate.setDate(inputDate.getDate() + 1);

                            // Set the new date value to the input field
                            document.getElementById('inputDate').value = inputDate.toISOString().slice(0,10);

                            showReport();
                       }
                       function previousDay(){
                            // Get the input date value
                            let inputDate = new Date(document.getElementById('inputDate').value);

                            // Add 1 day to the input date
                            inputDate.setDate(inputDate.getDate() - 1);

                            // Set the new date value to the input field
                            document.getElementById('inputDate').value = inputDate.toISOString().slice(0,10);

                            showReport();
                       }
                    </script>
                    ";
        if ($date) {
            $html .= "<form>
                        <div class='row mt-3'>
                            <div class='col-4'> </div>
                            <div class='col-4 card p-2'>
                                <div class='container-fluid'>
                                    <div class='row mb-1'>
                                        <input class='form-control' id='inputDate' name='date' type='date' value='$date'>
                                    </div>

                                    <div class='row mb-1'>
                                        <label><input name='reCalculate' $checked type='checkbox' value='1'> محاسبه مجدد</label>
                                    </div>

                                    <div class='row mb-1 d-flex justify-content-around'>
                                        <button class='btn btn-outline-info' onclick='previousDay()'>👉 روز قبل</button>
                                        <button class='btn btn-primary' type='submit' id='show_report'>نمایش گزارش</button>
                                        <button class='btn btn-outline-info' onclick='nextDay()'>روز بعد 👈</button>
                                    </div>
                                </div>
                            </div>
                            <div class='col-4'> </div>
                        </div>
                    </form>";
        }
        return $html;
    }

    public function publicReportCustomer()
    {
        $date = \Request()->date;
        if (isset(\Request()->reCalculate)){
            DB::table('orders')
                ->whereDate('created_at',$date)
                ->update(['items_count' => null]);
        }
        (new \Modules\Core\Helpers\Helpers)->updateOrdersCalculateData();


        $orders = DB::table('orders')
            ->whereIn('status',['new','delivered','in_progress'])
            ->whereDate('created_at',$date)
            ->whereNull('parent_id')
            ->get();

        foreach ($orders as $order) {
            $children = DB::table('orders')->where('parent_id',$order->id)->whereIn('status',['new','delivered','in_progress'])->pluck('id')->toArray();
            $order->children = implode(',',$children);
        }

        $html = $this->commonHtmlForReport($date);

        $persian_date = convertMiladiToShamsiWithoutTime($date);
        $html .= "<h4 class='text-center'>گزارش مشتریان تعداد تنوع فروش رفته در تاریخ  <div class='inline-date'>$persian_date</div></h4>";

        $html1 = "<table class='table table-hover table-striped'>";

        $html1 .= "<tr>";
        $html1 .= "<th>شناسه سفارش</th>";
        $html1 .= "<th>سفارشات فرزند</th>";
        $html1 .= "<th>استان</th>";
        $html1 .= "<th>شهرستان</th>";
        $html1 .= "<th>نام</th>";
        $html1 .= "<th>نام خانوادگی</th>";
        $html1 .= "<th>قیمت</th>";
        $html1 .= "<th>هزینه ارسال</th>";
        $html1 .= "<th>پرداخت از کیف پول</th>";
        $html1 .= "<th>تخفیف</th>";
        $html1 .= "<th>تخفیف با کوپن</th>";
        $html1 .= "<th>قابل پرداخت</th>";
        $html1 .= "<th>تعداد محصولات</th>";
        $html1 .= "<th>تعداد آیتم ها (تنوع)</th>";
        $html1 .= "<th>زمان</th>";
        $html1 .= "</tr>";

        $total_price = 0;
        $total_shipping = 0;
        $total_wallet = 0;
        $total_count = 0;
        $total_quantity = 0;
        $total_discount = 0;
        $total_payable = 0;

        foreach($orders as $item){

            $order_id = $item->id;
            $parent_id = $item->children;
            $province = $item->province;
            $city = $item->city;
            $first_name = $item->first_name;
            $last_name = $item->last_name;
            $has_coupon = $item->coupon_id?"1":"0";

            $total_price += $item->total_amount;
            $total_shipping += $item->shipping_amount;
            $total_wallet += $item->used_wallet_amount;
            $total_payable += $item->total_payable_amount;
            $total_count += $item->items_count;
            $total_quantity += $item->items_quantity;
            $total_discount += $item->discount_amount;

            $price = number_format($item->total_amount, 0 , '.' , ',' );
            $shipping = number_format($item->shipping_amount, 0 , '.' , ',' );
            $wallet = number_format($item->used_wallet_amount, 0 , '.' , ',' );
            $count = number_format($item->items_count, 0 , '.' , ',' );
            $quantity = number_format($item->items_quantity, 0 , '.' , ',' );
            $discount = number_format($item->discount_amount, 0 , '.' , ',' );
            $payable = number_format($item->total_payable_amount, 0 , '.' , ',' );
            $time = explode(" ",$item->created_at)[1];

            $c = $count>$quantity?'bg-warning':'';
            $html1 .= "<tr class='$c'>";
            $html1 .= "<td>$order_id</td>";
            $html1 .= "<td>$parent_id</td>";
            $html1 .= "<td>$province</td>";
            $html1 .= "<td>$city</td>";
            $html1 .= "<td>$first_name</td>";
            $html1 .= "<td>$last_name</td>";
            $html1 .= "<td>$price</td>";
            $html1 .= "<td>$shipping</td>";
            $html1 .= "<td>$wallet</td>";
            $html1 .= "<td>$discount</td>";
            $html1 .= "<td>$has_coupon</td>";
            $html1 .= "<td>$payable</td>";
            $html1 .= "<td>$count</td>";
            $html1 .= "<td>$quantity</td>";
            $html1 .= "<td>$time</td>";
            $html1 .= "</tr>";
        }

        $html1 .= "</table>";

        $html2 = "<table class='table table-hover table-striped'>";

        $html2 .= "<tr>";
        $html2 .= "<th>تعداد سفارشات</th>";
        $html2 .= "<th>مجموع فروش</th>";
        $html2 .= "<th>مجموع هزینه ارسال</th>";
        $html2 .= "<th>مجموع پرداخت از کیف پول</th>";
        $html2 .= "<th>تخفیف</th>";
        $html2 .= "<th>مجموع قابل پرداخت</th>";
        $html2 .= "<th>مجموع محصولات</th>";
        $html2 .= "<th>مجموع آیتم ها (تنوع)</th>";
        $html2 .= "</tr>";

        $total_orders_count = count($orders);

        $total_orders_count = number_format($total_orders_count, 0 , '.' , ',' );
        $total_price = number_format($total_price, 0 , '.' , ',' );
        $total_shipping = number_format($total_shipping, 0 , '.' , ',' );
        $total_wallet = number_format($total_wallet, 0 , '.' , ',' );
        $total_count = number_format($total_count, 0 , '.' , ',' );
        $total_quantity = number_format($total_quantity, 0 , '.' , ',' );
        $total_discount = number_format($total_discount, 0 , '.' , ',' );
        $total_payable = number_format($total_payable, 0 , '.' , ',' );

        $html2 .= "<tr>";
        $html2 .= "<td>$total_orders_count</td>";
        $html2 .= "<td>$total_price</td>";
        $html2 .= "<td>$total_shipping</td>";
        $html2 .= "<td>$total_wallet</td>";
        $html2 .= "<td>$total_discount</td>";
        $html2 .= "<td>$total_payable</td>";
        $html2 .= "<td>$total_count</td>";
        $html2 .= "<td>$total_quantity</td>";
        $html2 .= "</tr>";

        $html2 .= "</table>";

        $html .= "<hr>";
        $html .= $html2;
        $html .= "<hr>";
        $html .= $html1;

        echo "<div class='container'>";
        echo $html;
        echo "</div>";
    }
    public function publicReportVariety()
    {
        $date = \Request()->date;
        if (isset(\Request()->reCalculate)){
            DB::table('orders')
                ->whereDate('created_at',$date)
                ->update(['items_count' => null]);
        }
        (new \Modules\Core\Helpers\Helpers)->updateOrdersCalculateData();

        $orders = DB::table('orders')
            ->select('id')
            ->whereIn('status',['new','delivered','in_progress'])
            ->whereDate('created_at',$date)
            ->where('parent_id',null)
            ->pluck('id')
            ->toArray();

        $sub_orders = DB::table('orders')
            ->select('id')
            ->whereIn('parent_id',$orders)
            ->whereIn('status',['new','delivered','in_progress'])
            ->pluck('id')
            ->toArray();

        $mergedOrdersArray = array_merge($orders, $sub_orders);


        $report = DB::table('order_items as oi')
            ->join('products as p' ,'oi.product_id' , '=', 'p.id')
            ->leftJoin('varieties as v' ,'oi.variety_id' , '=', 'v.id')
            ->leftJoin('colors as c' ,'v.color_id' , '=', 'c.id')
            ->select(
                "p.id",
                "oi.variety_id",
                "oi.quantity",
                "oi.amount",
                "oi.discount_amount",
                DB::raw("oi.amount + oi.discount_amount as real_price"),
//                DB::raw("oi.amount * sum(oi.quantity) as total"),
                DB::raw("(oi.amount + oi.discount_amount) * sum(oi.quantity) as total"),
                "p.title",
                DB::raw("json_unquote(JSON_EXTRACT(oi.extra, '$.attributes[0].value')) as value"),
                DB::raw("json_unquote(JSON_EXTRACT(oi.extra, '$.attributes[0].label')) as label"),
                "c.name as color",
                DB::raw("sum(oi.quantity) as sum")
            )
            ->whereIn('order_id', $mergedOrdersArray)
            ->where('oi.status', 1)
            ->groupBy('oi.variety_id')
            ->get();

        $final_report = [];
        foreach ($report as $item) {
//            $data['id'] = $item->id;

            $title = [];
            $title[] = $item->title;
            if($item->label) {$title[] = $item->label;}
            if($item->value) {$title[] = $item->value;}
            if($item->color) {$title[] = "رنگ " . $item->color;}

            $data['id'] = $item->id;
            $data['variety_id'] = $item->variety_id;
            $data['title'] = implode(" | ", $title);
            $data['sell_quantity'] = $item->sum;
            $data['price'] = $item->real_price;
            $data['total_sale'] = $item->total;

            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport($date);

        $persian_date = convertMiladiToShamsiWithoutTime($date);
        $html .= "<h4 class='text-center'>گزارش تعداد تنوع فروش رفته در تاریخ  <div class='inline-date'>$persian_date</div></h4>";

        $html1 = "<table class='table table-hover table-striped'>";
        $html1 .= "<th>شناسه</th>";
        $html1 .= "<th>شناسه تنوع</th>";
        $html1 .= "<th>عنوان</th>";
        $html1 .= "<th>تعداد فروش</th>";
        $html1 .= "<th>قیمت</th>";
        $html1 .= "<th>جمع فروش</th>";
        $html1 .= "</tr>";

        $total_sell_quantity = 0;
        $total_price = 0;

        foreach($final_report as $item){
            $item_id = $item['id'];
            $variety_id = $item['variety_id'];
            $item_title = $item['title'];
            $item_quantity = $item['sell_quantity'];
            $item_price = $item['price'];
            $item_total_price = $item['total_sale'];

            $total_sell_quantity += $item_quantity;
            $total_price += $item_total_price;

            $item_id = number_format($item_id, 0 , '.' , ',' );
            $item_quantity = number_format($item_quantity, 0 , '.' , ',' );
            $item_price = number_format($item_price, 0 , '.' , ',' );
            $item_total_price = number_format($item_total_price, 0 , '.' , ',' );

            $html1 .= "<tr>";
            $html1 .= "<td>$item_id</td>";
            $html1 .= "<td>$variety_id</td>";
            $html1 .= "<td>$item_title</td>";
            $html1 .= "<td>$item_quantity</td>";
            $html1 .= "<td>$item_price</td>";
            $html1 .= "<td>$item_total_price</td>";
            $html1 .= "</tr>";
        }

        $html1 .= "</table>";

        $html2 = "<table class='table table-hover table-striped'>";

        $html2 .= "<tr>";
        $html2 .= "<th>تعداد تنوع</th>";
        $html2 .= "<th>مجموع قیمت</th>";
        $html2 .= "</tr>";

        $total_sell_quantity = number_format($total_sell_quantity, 0 , '.' , ',' );
        $total_price = number_format($total_price, 0 , '.' , ',' );

        $html2 .= "<tr>";
        $html2 .= "<td>$total_sell_quantity</td>";
        $html2 .= "<td>$total_price</td>";
        $html2 .= "</tr>";

        $html2 .= "</table>";

        $html .= "<hr>";
        $html .= $html2;
        $html .= "<hr>";
        $html .= $html1;
        echo $html;
    }
    public function publicReportFull()
    {
        $date = \Request()->date;
        if (isset(\Request()->reCalculate)){
            DB::table('orders')
                ->whereDate('created_at',$date)
                ->update(['items_count' => null]);
        }
        (new \Modules\Core\Helpers\Helpers)->updateOrdersCalculateData();

        $orders = DB::table('orders')
            ->select('id')
            ->whereIn('status',['new','delivered','in_progress'])
            ->whereDate('created_at',$date)
            ->where('parent_id',null)
            ->pluck('id')
            ->toArray();

        $sub_orders = DB::table('orders')
            ->select('id')
            ->whereIn('parent_id',$orders)
            ->whereIn('status',['new','delivered','in_progress'])
            ->pluck('id')
            ->toArray();

        $mergedOrdersArray = array_merge($orders, $sub_orders);


        $report = DB::table('order_items as oi')
            ->join('products as p' ,'oi.product_id' , '=', 'p.id')
            ->leftJoin('varieties as v' ,'oi.variety_id' , '=', 'v.id')
            ->leftJoin('colors as c' ,'v.color_id' , '=', 'c.id')
            ->select(
                "p.id",
                "oi.variety_id",
                "oi.order_id",
                "oi.quantity",
                "oi.amount",
                "oi.discount_amount",
                DB::raw("oi.amount + oi.discount_amount as real_price"),
                DB::raw("(oi.amount + oi.discount_amount) * oi.quantity as total"),
                "p.title",
                DB::raw("json_unquote(JSON_EXTRACT(oi.extra, '$.attributes[0].value')) as value"),
                DB::raw("json_unquote(JSON_EXTRACT(oi.extra, '$.attributes[0].label')) as label"),
                "c.name as color"
            )
            ->whereIn('order_id', $mergedOrdersArray)
            ->where('oi.status', 1)
            ->get();

        $final_report = [];
        foreach ($report as $item) {
//            $data['id'] = $item->id;

            $title = [];
            $title[] = $item->title;
            if($item->label) {$title[] = $item->label;}
            if($item->value) {$title[] = $item->value;}
            if($item->color) {$title[] = "رنگ " . $item->color;}

            $data['id'] = $item->id;
            $data['variety_id'] = $item->variety_id;
            $data['order_id'] = $item->order_id;
            $data['title'] = implode(" | ", $title);
            $data['sell_quantity'] = $item->quantity;
            $data['price'] = $item->real_price;
            $data['total_sale'] = $item->total;

            $data['order_check'] = Order::find($item->order_id)->parent_id??'-';

            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport($date);

        $persian_date = convertMiladiToShamsiWithoutTime($date);
        $html .= "<h4 class='text-center'>گزارش تعداد تنوع فروش رفته در تاریخ  <div class='inline-date'>$persian_date</div></h4>";

        $html1 = "<table class='table table-hover table-striped'>";
        $html1 .= "<tr>";
        $html1 .= "<th>شناسه</th>";
        $html1 .= "<th>شماره سفارش</th>";
        $html1 .= "<th>پدر</th>";
        $html1 .= "<th>شناسه تنوع</th>";
        $html1 .= "<th>عنوان</th>";
        $html1 .= "<th>تعداد فروش</th>";
        $html1 .= "<th>قیمت</th>";
        $html1 .= "<th>جمع فروش</th>";
        $html1 .= "</tr>";

        $total_sell_quantity = 0;
        $total_price = 0;

        foreach($final_report as $item){

            $item_id = $item['id'];
            $variety_id = $item['variety_id'];
            $item_order_id = $item['order_id'];
            $item_order_check = $item['order_check'];
            $item_title = $item['title'];
            $item_quantity = $item['sell_quantity'];
            $item_price = $item['price'];
            $item_total_price = $item['total_sale'];

            $total_sell_quantity += $item_quantity;
            $total_price += $item_total_price;

            $item_quantity = number_format($item_quantity, 0 , '.' , ',' );
            $item_price = number_format($item_price, 0 , '.' , ',' );
            $item_total_price = number_format($item_total_price, 0 , '.' , ',' );

            $html1 .= "<tr>";
            $html1 .= "<td>$item_id</td>";
            $html1 .= "<td>$item_order_id</td>";
            $html1 .= "<td>$item_order_check</td>";
            $html1 .= "<td>$variety_id</td>";
            $html1 .= "<td>$item_title</td>";
            $html1 .= "<td>$item_quantity</td>";
            $html1 .= "<td>$item_price</td>";
            $html1 .= "<td>$item_total_price</td>";
            $html1 .= "</tr>";
        }

        $html1 .= "</table>";

        $html2 = "<table class='table table-hover table-striped'>";

        $html2 .= "<tr>";
        $html2 .= "<th>تعداد تنوع</th>";
        $html2 .= "<th>مجموع قیمت</th>";
        $html2 .= "</tr>";

        $total_sell_quantity = number_format($total_sell_quantity, 0 , '.' , ',' );
        $total_price = number_format($total_price, 0 , '.' , ',' );

        $html2 .= "<tr>";
        $html2 .= "<td>$total_sell_quantity</td>";
        $html2 .= "<td>$total_price</td>";
        $html2 .= "</tr>";

        $html2 .= "</table>";

        $html .= "<hr>";
        $html .= $html2;
        $html .= "<hr>";
        $html .= $html1;
        echo $html;
    }

    public function publicWalletTransaction()
    {
        $date = \Request()->date;

        $report = DB::table('transactions as t')
            ->join('customers as c','t.payable_id','=','c.id')
            ->select(
                "c.id as customer_id",
                DB::raw("CONCAT(first_name, ' ', last_name) as full_name"),
                'mobile',
                't.amount',
                't.created_at'
            )
            ->where('confirmed',1)
            ->where('payable_type',"Modules\Customer\Entities\Customer")
            ->whereNull('meta') // افزایش هایی که شارژ هستند
            ->where('type','deposit')
            ->whereDate('t.created_at',$date)
            ->get();

        $final_report = [];
        foreach ($report as $item) {
            $data['id'] = $item->customer_id;
            $data['mobile'] = $item->mobile;
            $data['full_name'] = $item->full_name;
            $data['amount'] = $item->amount;
            $data['created_at'] = $item->created_at;
            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport($date);

        $persian_date = (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($date);
        $html .= "<h4 class='text-center'>گزارش شارژهای ثبت شده در تاریخ  <div class='inline-date'>$persian_date</div></h4>";

        $html1 = "<table class='table table-hover table-striped'>";
        $html1 .= "<tr>";
        $html1 .= "<th>شناسه مشتری</th>";
        $html1 .= "<th>موبایل</th>";
        $html1 .= "<th>نام و نام خانوادگی</th>";
        $html1 .= "<th>مبلغ</th>";
        $html1 .= "<th>تاریخ</th>";
        $html1 .= "</tr>";

        $total_charge = 0;

        foreach($final_report as $item){

            $item_id = $item['id'];
            $mobile = $item['mobile'];
            $full_name = $item['full_name'];
            $amount = $item['amount'];
            $created_at = $item['created_at'];

            $total_charge += $amount;

            $amount = number_format($amount, 0 , '.' , ',' );

            $html1 .= "<tr>";
            $html1 .= "<td>$item_id</td>";
            $html1 .= "<td>$mobile</td>";
            $html1 .= "<td>$full_name</td>";
            $html1 .= "<td>$amount</td>";
            $html1 .= "<td>$created_at</td>";
            $html1 .= "</tr>";
        }

        $html1 .= "</table>";

        $html2 = "<table class='table table-hover table-striped'>";

        $html2 .= "<tr>";
        $html2 .= "<th>مجموع شارژ</th>";
        $html2 .= "</tr>";

        $total_charge = number_format($total_charge, 0 , '.' , ',' );

        $html2 .= "<tr>";
        $html2 .= "<td>$total_charge</td>";
        $html2 .= "</tr>";

        $html2 .= "</table>";

        $html .= "<hr>";
        $html .= $html2;
        $html .= "<hr>";
        $html .= $html1;
        echo $html;
    }

    public function checkWallet()
    {

        $report = DB::table('transactions as t')
            ->join('customers as c','c.id','=','t.payable_id')
            ->join('wallets as w','c.id','=','w.holder_id')
            ->whereNotNull('t.meta')
            ->where('w.holder_type','Modules\Customer\Entities\Customer')
            ->where('t.payable_type','Modules\Customer\Entities\Customer')
            ->where('t.confirmed',1)
            ->select(
                't.id',
                't.meta',
                't.payable_id',
                'c.first_name',
                'c.last_name',
                'c.mobile',
                't.created_at',
                't.amount',
                'w.balance',
                'w.gift_balance'
            )
            ->groupBy('t.meta', 't.payable_id', DB::raw('date(t.created_at)'), 't.amount')
            ->orderBy('t.created_at')
            ->having(DB::raw('count(*)'),'>',1)
            ->paginate();

        $final_report = [];
        foreach ($report as $item) {

            $desc = $item->meta?json_decode($item->meta)->description??'':'-';

            $data['id'] = $item->id;
            $data['meta'] = $desc;
            $data['user'] = $item->payable_id . " " . $item->first_name . " " . $item->last_name . " ($item->mobile)";
            $data['user_id'] = $item->payable_id;
            $data['date'] = $item->created_at;
            $data['amount'] = $item->amount;
            $data['balance'] = $item->balance;
            $data['gift_balance'] = $item->gift_balance;
            $data['status'] = $item->balance==$this->calculateWallet($item->payable_id)?'<div class="badge badge-success" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی دارد</div>':'<div class="badge badge-warning" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی ندارد</div>';

            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport();

        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";

        $html .= "<h4 class='text-center'>گزارش تراکنش های تکراری</h4>";

        $html .= "<table class='table table-hover table-striped'>";
        $html .= "<tr>";
        $html .= "<th>شناسه</th>";
        $html .= "<th>شرح</th>";
        $html .= "<th>کاربر</th>";
        $html .= "<th>تاریخ</th>";
        $html .= "<th>مبلغ</th>";
        $html .= "<th>موجودی کیف پول</th>";
        $html .= "<th>موجودی هدیه</th>";
        $html .= "<th>وضعیت</th>";
        $html .= "<th>عملیات</th>";
        $html .= "</tr>";

        foreach($final_report as $item){

            $id = $item['id'];
            $meta = $item['meta'];
            $user = $item['user'];
            $user_id = $item['user_id'];
            $date = $item['date'];
            $persian_date = (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($date);
            $amount = $item['amount'];
            $balance = $item['balance'];
            $gift_balance = $item['gift_balance'];
            $status = $item['status'];

            $amount = number_format($amount, 0 , '.' , ',' );
            $balance = number_format($balance, 0 , '.' , ',' );
            $gift_balance = number_format($gift_balance, 0 , '.' , ',' );

            $html .= "<tr>";
            $html .= "<td>$id</td>";
            $html .= "<td>$meta</td>";
            $html .= "<td>$user</td>";
            $html .= "<td><div style='direction: ltr'>$date | $persian_date</div></td>";
            $html .= "<td>$amount</td>";
            $html .= "<td>$balance</td>";
            $html .= "<td>$gift_balance</td>";
            $html .= "<td>$status</td>";
            $html .= "<td>
                            <button class='btn btn-outline-info btn-sm check-data' data-id='$user_id' data-type='transaction'>🔍</button>
                        </td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $html .= $report->links('pagination::bootstrap-4');

        $html .= "<div class='alert border-info mb-5 w-75' id='customer-result' style='margin: 0 auto'></div>";

        $route = '/v1/get-customer-transaction';
        $route_update = '/v1/change-transaction-status';
        $route_update_wallet = '/v1/update-wallet';
        $html .= "
            <script>
                $(document).ready(function(){
                    $('.check-data').click(function(){
                        let id = $(this).data('id');
                        $.ajax({
                            url: '$route',
                            data: {
                                id: id
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('transaction 4');
                            },
                            success: function(data){
                                let h = '<table class=\'table table-hover table-striped\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>شناسه</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>تاریخ</th>';
                                h += '<th>شرح</th>';
                                h += '<th>نوع</th>';
                                h += '<th>مبلغ</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                let transactions = data['transactions'];
                                for (let i = 0 ; i < transactions.length ; i++){
                                    let description = transactions[i]['meta']?transactions[i]['meta']['description']:'-';
                                    h += '<tr>';
                                    h += '<td>'+transactions[i]['id']+'</td>';
                                    h += '<td>'+transactions[i]['status']+'</td>';
                                    h += '<td>'+transactions[i]['date']+'</td>';
                                    h += '<td>'+description+'</td>';
                                    h += '<td>'+transactions[i]['type']+'</td>';
                                    h += '<td>'+transactions[i]['amount']+'</td>';
                                    h += '</tr>';
                                }
                                h += '</tbody>';
                                h += '</table>';

                                h += '<table class=\'table table-hover table-striped mt-3\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>موجودی کیف پول</th>';
                                h += '<th>موجودی هدیه قدیم</th>';
                                h += '<th>موجودی جدید</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>عملیات</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                h += '<tr>';
                                h += '<td id=\'update_balance\'>'+data['balance']+'</td>';
                                h += '<td id=\'update_gift_balance\'>'+data['gift_balance']+'</td>';
                                h += '<td id=\'update_new_balance\'>'+data['new_balance']+'</td>';
                                h += '<td id=\'update_status\'>'+data['status']+'</td>';
                                let b = data['new_balance_raw'];
                                let cid = data['customer_id'];
                                h += '<td>'+'<button class=\'btn btn-outline-success btn-sm\' id=\'save-wallet\' data-customer_id=\''+cid+'\' data-balance=\''+b+'\'>💾</button>'+'</td>';
                                h += '</tr>';
                                h += '</tbody>';
                                h += '</table>';

                                $('#customer-result').html(h);

                                if (transactions.length == 2){
                                    //console.log('میتونه اتوماتیک باشه');
                                    $('.badge-success.change-status:eq(1)').trigger('click');
                                }
                            }
                        });
                    });
                });

                $(document).on('click','.change-status',function (){
                    let item_id = $(this).data('id');
                    let item = $(this);
                        $.ajax({
                            url: '$route_update',
                            data: {
                                id: item_id
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('part 1');
                            },
                            success: function(data){
                                item.closest('td').html(data['transaction']['status']);

                                $('#update_balance').html(data['balance']);
                                $('#update_gift_balance').html(data['gift_balance']);
                                $('#update_new_balance').html(data['new_balance']);
                                $('#update_status').html(data['status']);
                                $('#save-wallet').attr('data-balance',data['new_balance_raw']);

                                if (data['is_equal']){
                                    $('#save-wallet').trigger('click');
                                    $('#save-wallet').slideUp();
                                } else {
                                    $('#save-wallet').slideDown();
                                }
                            }
                        });
                });

                $(document).on('click','#save-wallet',function (){
                    $.ajax({
                        url: '$route_update_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });
            </script>
        ";

        echo $html;
    }

    public function checkDifference()
    {
        $report = DB::table('transactions as t')
            ->join('customers as c','c.id','=','t.payable_id')
            ->join('wallets as w','c.id','=','w.holder_id')
            ->select(
                't.payable_id',
                'c.first_name',
                'c.last_name',
                'c.mobile',
                DB::raw('sum(t.amount) as transaction_amount'),
                'w.balance',
            )
            ->where('w.holder_type','Modules\Customer\Entities\Customer')
            ->where('t.payable_type','Modules\Customer\Entities\Customer')
            ->where('t.confirmed',1)
            ->groupBy('t.payable_id')
            ->having(DB::raw('SUM(amount)'),'<>',DB::raw('balance'))
            ->paginate();

        $final_report = [];
        foreach ($report as $item) {

            $data['id'] = $item->payable_id;
            $data['full_name'] = $item->first_name . " " . $item->last_name;
            $data['mobile'] = $item->mobile;
            $data['balance'] = $item->balance;
            $data['transaction_amount'] = $item->transaction_amount;
            $data['status'] = $item->balance==$item->transaction_amount?'<div class="badge badge-success" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی دارد</div>':'<div class="badge badge-warning" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی ندارد</div>';

            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport();

        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";

        $html .= "<h4 class='text-center'>گزارش تفاوت کیف پول و تراکنش ها</h4>";

        $html .= "<table class='table table-hover table-striped'>";
        $html .= "<tr>";
        $html .= "<th>شناسه مشتری</th>";
        $html .= "<th>نام و نام خانوادگی</th>";
        $html .= "<th>موبایل</th>";
        $html .= "<th>موجودی کیف پول</th>";
        $html .= "<th>نتیجه تراکنش ها</th>";
        $html .= "<th>وضعیت</th>";
        $html .= "<th>عملیات</th>";
        $html .= "</tr>";

        foreach($final_report as $item){

            $id = $item['id'];
            $full_name = $item['full_name'];
            $balance = $item['balance'];
            $mobile = $item['mobile'];
            $transaction_amount = $item['transaction_amount'];
            $status = $item['status'];

            $transaction_amount = number_format($transaction_amount, 0 , '.' , ',' );
            $balance = number_format($balance, 0 , '.' , ',' );

            $html .= "<tr>";
            $html .= "<td>$id</td>";
            $html .= "<td>$full_name</td>";
            $html .= "<td>$mobile</td>";;
            $html .= "<td>$balance</td>";
            $html .= "<td>$transaction_amount</td>";
            $html .= "<td>$status</td>";
            $html .= "<td>
                            <button class='btn btn-outline-info btn-sm check-data' data-id='$id' data-type='customer'>🔍</button>
                        </td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $html .= $report->links('pagination::bootstrap-4');

        $html .= "<div class='alert border-info mb-5 w-75' id='customer-result' style='margin: 0 auto'></div>";

        $route = '/v1/get-customer-transaction';
        $route_update = '/v1/change-transaction-status';
        $route_update_wallet = '/v1/update-wallet';
        $html .= "
            <script>
                $(document).ready(function(){
                    $('.check-data').click(function(){
                        let id = $(this).data('id');
                        let type = $(this).data('type');
                        $.ajax({
                            url: '$route',
                            data: {
                                id: id,
                                type: type
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('transaction 1');
                            },
                            success: function(data){
                                let h = '<table class=\'table table-hover table-striped\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>شناسه</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>تاریخ</th>';
                                h += '<th>شرح</th>';
                                h += '<th>نوع</th>';
                                h += '<th>مبلغ</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                let transactions = data['transactions'];
                                for (let i = 0 ; i < transactions.length ; i++){
                                    let description = transactions[i]['meta']?transactions[i]['meta']['description']:'-';
                                    h += '<tr>';
                                    h += '<td>'+transactions[i]['id']+'</td>';
                                    h += '<td>'+transactions[i]['status']+'</td>';
                                    h += '<td>'+transactions[i]['date']+'</td>';
                                    h += '<td>'+description+'</td>';
                                    h += '<td>'+transactions[i]['type']+'</td>';
                                    h += '<td>'+transactions[i]['amount']+'</td>';
                                    h += '</tr>';
                                }
                                h += '</tbody>';
                                h += '</table>';

                                h += '<table class=\'table table-hover table-striped mt-3\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>موجودی کیف پول</th>';
                                h += '<th>موجودی هدیه قدیم</th>';
                                h += '<th>موجودی جدید</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>عملیات</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                h += '<tr>';
                                h += '<td id=\'update_balance\'>'+data['balance']+'</td>';
                                h += '<td id=\'update_gift_balance\'>'+data['gift_balance']+'</td>';
                                h += '<td id=\'update_new_balance\'>'+data['new_balance']+'</td>';
                                h += '<td id=\'update_status\'>'+data['status']+'</td>';
                                let b = data['new_balance_raw'];
                                let cid = data['customer_id'];
                                h += '<td>'+'<button class=\'btn btn-outline-success btn-sm\' id=\'save-wallet\' data-customer_id=\''+cid+'\' data-balance=\''+b+'\'>💾</button>'+'</td>';
                                h += '</tr>';
                                h += '</tbody>';
                                h += '</table>';

                                $('#customer-result').html(h);
                            }
                        });
                    });
                });

                $(document).on('click','.change-status',function (){
                    let item_id = $(this).data('id');
                    let item = $(this);
                        $.ajax({
                            url: '$route_update',
                            data: {
                                id: item_id
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('part 2');
                            },
                            success: function(data){
                                item.closest('td').html(data['transaction']['status']);

                                $('#update_balance').html(data['balance']);
                                $('#update_gift_balance').html(data['gift_balance']);
                                $('#update_new_balance').html(data['new_balance']);
                                $('#update_status').html(data['status']);
                                $('#save-wallet').attr('data-balance',data['new_balance_raw']);
                            }
                        });
                });

                $(document).on('click','#save-wallet',function (){
                    $.ajax({
                        url: '$route_update_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });
            </script>
        ";

        echo $html;
    }

    public function checkWrongWallet()
    {
        $report = DB::table('customers as c')
            ->join('wallets as w','c.id','=','w.holder_id')
            ->select(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.mobile',
                'w.balance',
                'w.gift_balance',
            )
            ->where('w.balance','<' ,0)
            ->orWhere('w.balance','<' ,DB::raw('w.gift_balance'))
            ->paginate();

        $final_report = [];
        foreach ($report as $item) {

            $data['id'] = $item->id;
            $data['full_name'] = $item->first_name . " " . $item->last_name;
            $data['mobile'] = $item->mobile;
            $data['balance'] = $item->balance;
            $data['gift_balance'] = $item->gift_balance;
            $data['status'] = $item->balance<0?'<div class="badge badge-warning" style="font-size: 120%;">موجودی کیف پول منفی است</div>':'<div class="badge badge-warning" style="font-size: 120%;">موجودی کیف پول از موجودی هدیه کمتر است</div>';

            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport();

        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";

        $html .= "<h4 class='text-center'>گزارش موجودی اشتباه در کیف پول</h4>";

        $html .= "<table class='table table-hover table-striped'>";
        $html .= "<tr>";
        $html .= "<th>شناسه مشتری</th>";
        $html .= "<th>نام و نام خانوادگی</th>";
        $html .= "<th>موبایل</th>";
        $html .= "<th>موجودی کیف پول</th>";
        $html .= "<th>مقدار هدیه کیف پول</th>";
        $html .= "<th>وضعیت</th>";
        $html .= "<th>عملیات</th>";
        $html .= "</tr>";

        foreach($final_report as $item){

            $id = $item['id'];
            $full_name = $item['full_name'];
            $balance = $item['balance'];
            $mobile = $item['mobile'];
            $gift_balance = $item['gift_balance'];
            $status = $item['status'];

            $gift_balance = number_format($gift_balance, 0 , '.' , ',' );
            $balance = number_format($balance, 0 , '.' , ',' );

            $html .= "<tr>";
            $html .= "<td>$id</td>";
            $html .= "<td>$full_name</td>";
            $html .= "<td>$mobile</td>";;
            $html .= "<td>$balance</td>";
            $html .= "<td>$gift_balance</td>";
            $html .= "<td>$status</td>";
            $html .= "<td>
                            <button class='btn btn-outline-info btn-sm check-data' data-id='$id' data-type='customer'>🔍</button>
                        </td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $html .= $report->links('pagination::bootstrap-4');

        $html .= "<div class='alert border-info mb-5 w-75' id='customer-result' style='margin: 0 auto'></div>";

        $route = '/v1/get-customer-transaction';
        $route_update = '/v1/change-transaction-status';
        $route_update_wallet = '/v1/update-wallet';
        $html .= "
            <script>
                $(document).ready(function(){
                    $('.check-data').click(function(){
                        let id = $(this).data('id');
                        let type = $(this).data('type');
                        $.ajax({
                            url: '$route',
                            data: {
                                id: id,
                                type: type
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('transaction 2');
                            },
                            success: function(data){
                                let h = '<table class=\'table table-hover table-striped\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>شناسه</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>تاریخ</th>';
                                h += '<th>شرح</th>';
                                h += '<th>نوع</th>';
                                h += '<th>مبلغ</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                let transactions = data['transactions'];
                                for (let i = 0 ; i < transactions.length ; i++){
                                    let description = transactions[i]['meta']?transactions[i]['meta']['description']:'-';
                                    h += '<tr>';
                                    h += '<td>'+transactions[i]['id']+'</td>';
                                    h += '<td>'+transactions[i]['status']+'</td>';
                                    h += '<td>'+transactions[i]['date']+'</td>';
                                    h += '<td>'+description+'</td>';
                                    h += '<td>'+transactions[i]['type']+'</td>';
                                    h += '<td>'+transactions[i]['amount']+'</td>';
                                    h += '</tr>';
                                }
                                h += '</tbody>';
                                h += '</table>';

                                h += '<table class=\'table table-hover table-striped mt-3\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>موجودی کیف پول</th>';
                                h += '<th>موجودی هدیه قدیم</th>';
                                h += '<th>موجودی جدید</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>عملیات</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                h += '<tr>';
                                h += '<td id=\'update_balance\'>'+data['balance']+'</td>';
                                h += '<td id=\'update_gift_balance\'>'+data['gift_balance']+'</td>';
                                h += '<td id=\'update_new_balance\'>'+data['new_balance']+'</td>';
                                h += '<td id=\'update_status\'>'+data['status']+'</td>';
                                let b = data['new_balance_raw'];
                                let cid = data['customer_id'];
                                h += '<td>'+'<button class=\'btn btn-outline-success btn-sm\' id=\'save-wallet\' data-customer_id=\''+cid+'\' data-balance=\''+b+'\'>💾</button>'+'</td>';
                                h += '</tr>';
                                h += '</tbody>';
                                h += '</table>';

                                $('#customer-result').html(h);
                            }
                        });
                    });
                });

                $(document).on('click','.change-status',function (){
                    let item_id = $(this).data('id');
                    let item = $(this);
                        $.ajax({
                            url: '$route_update',
                            data: {
                                id: item_id
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('part 3');
                            },
                            success: function(data){
                                item.closest('td').html(data['transaction']['status']);

                                $('#update_balance').html(data['balance']);
                                $('#update_gift_balance').html(data['gift_balance']);
                                $('#update_new_balance').html(data['new_balance']);
                                $('#update_status').html(data['status']);
                                $('#save-wallet').attr('data-balance',data['new_balance_raw']);
                            }
                        });
                });

                $(document).on('click','#save-wallet',function (){
                    $.ajax({
                        url: '$route_update_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });
            </script>
        ";

        echo $html;
    }

    public function checkGiftWallet()
    {
        $report = DB::table('customers as c')
            ->join('wallets as w','c.id','=','w.holder_id')
            ->leftJoin('orders as o','c.id','=','o.customer_id')
            ->where('w.holder_type','Modules\Customer\Entities\Customer')
            ->whereNull('parent_id')
            ->select(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.mobile',
                'w.balance',
                'w.gift_balance',
                DB::raw('count(c.id) as count'),
            )
            ->groupBy('c.id')
            ->paginate(10000);

        // افزودن گزینه نمایش یا عدم نمایش ردیف برای هر آیتم مجموعه
        foreach ($report as $item) {
            $calculated_gift_amount = $this->CalculateGiftAmountOfCustomer($item->id);
            $item->show_row = $item->gift_balance==$calculated_gift_amount;
        }
        // فیلتر کردن آنهایی که قابل نمایش هستند
        $filtered_report = $report->filter(function($r) {
            return !$r->show_row;
        });

        $final_report = [];
        foreach ($filtered_report as $item) {

            $calculated_gift_amount = $this->CalculateGiftAmountOfCustomer($item->id);

            $data['id'] = $item->id;
            $data['full_name'] = $item->first_name . " " . $item->last_name;
            $data['mobile'] = $item->mobile;
            $data['total_orders'] = $item->count;
            $data['balance'] = $item->balance;
            $data['gift_balance'] = $item->gift_balance;
            $data['calculated_gift_amount'] = $calculated_gift_amount;
            $data['status'] = $item->gift_balance==$calculated_gift_amount?'<div class="badge badge-success" style="font-size: 120%;">هدیه کیف پول و مقدار محاسبه شده با هم همخوانی دارد</div>':'<div class="badge badge-warning" style="font-size: 120%;">هدیه کیف پول و مقدار محاسبه شده با هم همخوانی ندارد</div>';
            $final_report[] = $data;
        }

        $html = $this->commonHtmlForReport();

        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";

        $html .= "<h4 class='text-center'>گزارش تفاوت کیف پول و تراکنش ها</h4>";

        $html .= "<table class='table table-hover table-striped'>";
        $html .= "<tr>";
        $html .= "<th>شناسه مشتری</th>";
        $html .= "<th>نام و نام خانوادگی</th>";
        $html .= "<th>موبایل</th>";
        $html .= "<th>تعداد سفارشات</th>";
        $html .= "<th>موجودی کیف پول</th>";
        $html .= "<th>هدیه کیف پول</th>";
        $html .= "<th>هدیه های محاسبه شده</th>";
        $html .= "<th>وضعیت</th>";
        $html .= "<th>عملیات</th>";
        $html .= "</tr>";

        foreach($final_report as $item){

            $id = $item['id'];
            $full_name = $item['full_name'];
            $balance = $item['balance'];
            $gift_balance = $item['gift_balance'];
            $mobile = $item['mobile'];
            $total_orders = $item['total_orders'];
            $calculated_gift_amount_raw = $item['calculated_gift_amount'];
            $calculated_gift_amount = $item['calculated_gift_amount'];
            $status = $item['status'];

            $calculated_gift_amount = number_format($calculated_gift_amount, 0 , '.' , ',' );
            $gift_balance = number_format($gift_balance, 0 , '.' , ',' );
            $balance = number_format($balance, 0 , '.' , ',' );

            $html .= "<tr>";
            $html .= "<td>$id</td>";
            $html .= "<td>$full_name</td>";
            $html .= "<td>$mobile</td>";;
            $html .= "<td>$total_orders</td>";;
            $html .= "<td>$balance</td>";
            $html .= "<td>$gift_balance</td>";
            $html .= "<td>$calculated_gift_amount</td>";
            $html .= "<td>$status</td>";
            $html .= "<td>
                            <button class='btn btn-outline-info btn-sm check-data' data-id='$id' data-type='customer'>🔍</button>
                            <button class='btn btn-outline-success btn-sm save-gift-data' data-customer_id='$id' data-balance='$calculated_gift_amount_raw'>💾</button>
                        </td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $html .= $report->links('pagination::bootstrap-4');

        $html .= "<div class='alert border-info mb-5 w-75' id='customer-result' style='margin: 0 auto'></div>";

        $route = '/v1/get-customer-transaction';
        $route_update = '/v1/change-transaction-status';
        $route_update_wallet = '/v1/update-wallet';
        $route_update_gift_wallet = '/v1/update-gift-wallet';

        if(\request()->autorun == 1){

            $customer_id = $final_report[4]['id'];
            $balance = $final_report[4]['calculated_gift_amount'];
            $wallet = Wallet::where('holder_type', 'Modules\Customer\Entities\Customer')
                ->where('holder_id', $customer_id)
                ->first();
            $wallet->gift_balance = $balance;
            $wallet->save();

            $html .= "
            <script>
                $(document).ready(function(){
                    setTimeout(function (){
                        location.reload();
                    },5000)
                });
            </script>
            ";
        }

        $html .= "
            <script>
                $(document).ready(function(){
                    $('.check-data').click(function(){
                        let id = $(this).data('id');
                        let type = $(this).data('type');
                        $.ajax({
                            url: '$route',
                            data: {
                                id: id,
                                type: type,
                                update_type: 'gift_balance',
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('transaction 3');
                            },
                            success: function(data){
                                let h = '<table class=\'table table-hover table-striped\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>شناسه</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>تاریخ</th>';
                                h += '<th>شرح</th>';
                                h += '<th>نوع</th>';
                                h += '<th>نوع تراکنش واریزی</th>';
                                h += '<th>مبلغ</th>';
                                h += '</tr>';
                                h += '</thead>';
                                h += '<tbody>';
                                let transactions = data['transactions'];
                                for (let i = 0 ; i < transactions.length ; i++){
                                    let description = transactions[i]['meta']?transactions[i]['meta']['description']:'-';
                                    h += '<tr>';
                                    h += '<td>'+transactions[i]['id']+'</td>';
                                    h += '<td>'+transactions[i]['status']+'</td>';
                                    h += '<td>'+transactions[i]['date']+'</td>';
                                    h += '<td>'+description+'</td>';
                                    h += '<td>'+transactions[i]['type']+'</td>';
                                    h += '<td>'+transactions[i]['charge_type']+'</td>';
                                    h += '<td>'+transactions[i]['amount']+'</td>';
                                    h += '</tr>';
                                }
                                h += '</tbody>';
                                h += '</table>';

                                h += '<table class=\'table table-hover table-striped mt-3\'>';
                                h += '<thead>';
                                h += '<tr>';
                                h += '<th>موجودی کیف پول</th>';
                                h += '<th>موجودی هدیه قدیم</th>';
                                h += '<th>موجودی هدیه جدید</th>';
                                h += '<th>وضعیت</th>';
                                h += '<th>عملیات</th>';
                                h += '</tr>';
                                h += '</thead>';

                                h += '<tbody>';
                                h += '<tr>';
                                h += '<td id=\'update_balance\'>'+data['balance']+'</td>';
                                h += '<td id=\'update_gift_balance\'>'+data['gift_balance']+'</td>';
                                h += '<td id=\'update_new_gift_balance\'>'+data['new_gift_balance']+'</td>';
                                h += '<td id=\'update_status\'>'+data['gift_status']+'</td>';
                                let b = data['new_gift_balance_raw'];
                                let cid = data['customer_id'];
                                h += '<td>'+'<button class=\'btn btn-outline-success btn-sm\' id=\'save-gift-wallet\' data-customer_id=\''+cid+'\' data-balance=\''+b+'\'>💾</button>'+'</td>';
                                h += '</tr>';
                                h += '</tbody>';
                                h += '</table>';

                                $('#customer-result').html(h);

                                $('html, body').animate({ scrollTop: $(document).height() }, 'slow');
                            }
                        });
                    });
                });

                $(document).on('click','.change-status',function (){
                    let item_id = $(this).data('id');
                    let item = $(this);
                        $.ajax({
                            url: '$route_update',
                            data: {
                                id: item_id
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('part 4');
                            },
                            success: function(data){
                                item.closest('td').html(data['transaction']['status']);

                                $('#update_balance').html(data['balance']);
                                $('#update_gift_balance').html(data['gift_balance']);
                                $('#update_new_balance').html(data['new_balance']);
                                $('#update_status').html(data['status']);
                                $('#save-wallet').attr('data-balance',data['new_balance_raw']);
                            }
                        });
                });

                $(document).on('click','#save-wallet',function (){
                    $.ajax({
                        url: '$route_update_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });

                $(document).on('click','#save-gift-wallet',function (){
                    $.ajax({
                        url: '$route_update_gift_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });

                $(document).on('click','.save-gift-data',function (){
                    $.ajax({
                        url: '$route_update_gift_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });
            </script>
        ";

        echo $html;
    }

    public function getCustomerTransactions(){

        $helper = new Helpers();

        $id = \request()->id;
        $status_type = (\request()->update_type && \request()->update_type == 'gift_balance')?'gift_wallet':'wallet';
        $transactions = DB::table('transactions')
            ->where('payable_type','Modules\Customer\Entities\Customer')
            ->where('payable_id',$id)
            ->get();
        $new_balance = 0;
        foreach ($transactions as $transaction) {
            if ($status_type == 'wallet'){
                $transaction->status = $transaction->confirmed?"<span class='badge badge-success change-status' data-id='$transaction->id' style='font-size: 100%'>موفق</span>":"<span class='badge badge-danger change-status' data-id='$transaction->id' style='font-size: 100%'>ناموفق</span>";
            } elseif ('gift_wallet'){
                $transaction->status = $transaction->confirmed?"<span class='badge badge-success' style='font-size: 100%'>موفق</span>":"<span class='badge badge-danger' style='font-size: 100%'>ناموفق</span>";
            }

            $dt = explode(" ",$transaction->created_at);
            $transaction->date = "<div style='direction: ltr'>".implode(" " , [$helper->convertMiladiToShamsi($dt[0]),$dt[1]])."</div>";
            $transaction->meta = json_decode($transaction->meta);
            $transaction->type = $transaction->type=='deposit'?'واریز ✅':'برداشت 🔺';
            $transaction->charge_type = $this->getChargeTypeById($transaction->charge_type_id);
            $new_balance += $transaction->confirmed==1?$transaction->amount:0;
            $transaction->amount = number_format($transaction->amount, 0 , '.' , ',' );
        }

        $wallet = DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $id)
            ->first();

        $new_gift_balance = $this->CalculateGiftAmountOfCustomer($id);

        $data = [
            'customer_id' => $id,
            'transactions' => $transactions,
            'balance' => number_format($wallet->balance, 0 , '.' , ',' ),
            'gift_balance' => number_format($wallet->gift_balance, 0 , '.' , ',' ),
            'new_balance' => number_format($new_balance, 0 , '.' , ',' ),
            'new_gift_balance' => number_format($new_gift_balance, 0 , '.' , ',' ),
            'new_balance_raw' => $new_balance,
            'new_gift_balance_raw' => $new_gift_balance,
        ];

        if ($status_type == 'wallet'){
            $data['status'] = $new_balance==$wallet->balance?'<div class="badge badge-success" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی دارد</div>':'<div class="badge badge-warning" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی ندارد</div>';
        } elseif ('gift_wallet'){
            $data['gift_status'] = $new_gift_balance==$wallet->gift_balance?'<div class="badge badge-success" style="font-size: 120%;">موجودی هدیه کیف پول و هدیه محاسبه شده با هم همخوانی دارد</div>':'<div class="badge badge-warning" style="font-size: 120%;">موجودی هدیه کیف پول و هدیه محاسبه شده با هم همخوانی ندارد</div>';
        }

        return response()->json($data);
    }

    public function changeTransactionsStatus(){

        $id = \request()->id;
        $transaction = Transaction::find($id);

        $transaction->confirmed = $transaction->confirmed==1?0:1;
        $transaction->save();

        $transaction->balance = $this->calculateWallet($transaction->payable_id);
        $transaction->status = $transaction->confirmed?"<span class='badge badge-success change-status' data-id='$transaction->id' style='font-size: 100%'>موفق</span>":"<span class='badge badge-danger change-status' data-id='$transaction->id' style='font-size: 100%'>ناموفق</span>";

        $wallet = DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $transaction->payable_id)
            ->first();

        $data = [
            'transaction' => $transaction,
            'balance' => number_format($wallet->balance, 0 , '.' , ',' ),
            'gift_balance' => number_format($wallet->gift_balance, 0 , '.' , ',' ),
            'new_balance' => number_format($transaction->balance, 0 , '.' , ',' ),
            'new_balance_raw' => $transaction->balance,
            'status' => $transaction->balance==$wallet->balance?'<div class="badge badge-success" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی دارد</div>':'<div class="badge badge-warning" style="font-size: 120%;">موجودی کیف پول و تراکنش ها با هم همخوانی ندارد</div>',
            'is_equal' => $transaction->balance==$wallet->balance,
            'done' => true,
        ];

        return response()->json($data);
    }

    public function updateWallet(){

        $customer_id = \request()->customer_id;
        $balance = \request()->balance;
        $wallet = Wallet::where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $customer_id)
            ->first();
        $wallet->balance = $balance;
        $wallet->save();

        return response()->success('با موفقیت ثبت شد');
    }

    public function updateGiftWallet(){

        $customer_id = \request()->customer_id;
        $balance = \request()->balance;
        $wallet = Wallet::where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $customer_id)
            ->first();
        $wallet->gift_balance = $balance;
        $wallet->save();

        return response()->success('با موفقیت ثبت شد');
    }

    public function calculateWallet($customer_id){
        $transactions = DB::table('transactions')
            ->where('payable_type','Modules\Customer\Entities\Customer')
            ->where('payable_id',$customer_id)
            ->where('confirmed',1)
            ->get();

        $balance = 0;
        $gift_balance = 0;
        foreach ($transactions as $transaction) {
//            if ($transaction->type=='deposit'){
                $balance += $transaction->amount;
//            } else {
//                $balance -= $transaction->amount;
//            }
        }
        return $balance;
    }

    public function CalculateGiftAmountOfCustomer($customer_id)
    {
        $gift_statuses = DB::table('charge_types')->where('is_gift',1)->pluck('id')->toArray();

//        $customer = DB::table('customers')->where('id',$customer_id)->first();
        $transactions = DB::table('transactions')
            ->where('payable_type','Modules\Customer\Entities\Customer')
            ->where('payable_id',$customer_id)
            ->where('confirmed',1)
            ->get();

        $balance = 0;
        $gift_balance = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->type=='deposit'){
                $balance += $transaction->amount;
//                dump($transaction->charge_type_id . " , " .implode("|",$gift_statuses));
                if (in_array($transaction->charge_type_id,$gift_statuses)){
                    // در صورتی که تراکنش از نوع هدیه باشد موجودی هدیه افزایش پیدا می کند
                    $gift_balance += $transaction->amount;
                }
//                dump($gift_balance . ' +');
            } else {
                $balance += $transaction->amount;
                $gift_balance += $transaction->amount;
                if ($gift_balance < 0){
                    // در صورتی که میزان برداشت از موجودی هدیه کیپول باشد آن را کمتراز صفر می کند که در این حالت مقدار آن را برابر صفر درنظر می گیریم
                    $gift_balance = 0;
                }
//                dump($gift_balance . ' -');
            }
        }
//        dump($gift_balance,$balance);
        return $gift_balance;
    }

    public function getChargeTypeById($charge_type_id){
        return DB::table('charge_types')->where('id',$charge_type_id)->value('title')??'-';
    }

    public function checkAllDuplicates()
    {
        $list = DB::table('transactions')
            ->select(
                'id',
                'payable_id',
                DB::raw('count(meta) as count_items'),
                DB::raw('sum(confirmed) as confirmed_total'),
                DB::raw("CAST(json_unquote(JSON_EXTRACT(meta, '$.description')) as CHAR) as meta"),
                'created_at',
                'amount',
            )
            ->where('payable_type','Modules\Customer\Entities\Customer')
            ->whereRaw("CAST(json_unquote(JSON_EXTRACT(meta, '$.description')) as CHAR) like 'هدیه خرید سفارش %'")
            ->groupBy('meta')
            ->having(DB::raw('count_items'),'=','2')
            ->having(DB::raw('confirmed_total'),'>','1')
            ->paginate();

        $html = $this->commonHtmlForReport();
        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";
        $html .= "<h4 class='text-center'>گزارش تراکنش های تکراری جهت مدیریت اتوماتیک</h4>";

        $html .= "<table class='table table-hover table-striped'>";
        $html .= "<tr>";
        $html .= "<th>شناسه تراکنش</th>";
        $html .= "<th>شرح</th>";
        $html .= "<th>کاربر</th>";
        $html .= "<th>تاریخ</th>";
        $html .= "<th>مبلغ</th>";
        $html .= "<th>عملیات</th>";
        $html .= "</tr>";

        foreach($list as $item){

            $id = $item->id;
            $meta = $item->meta;
            $user_id = $item->payable_id;
            $date = $item->created_at;
            $persian_date = (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($date);
            $amount = $item->amount;

            $amount = number_format($amount, 0 , '.' , ',' );

            $html .= "<tr>";
            $html .= "<td>$id</td>";
            $html .= "<td>$meta</td>";
            $html .= "<td>$user_id</td>";
            $html .= "<td><div style='direction: ltr'>$date | $persian_date</div></td>";
            $html .= "<td>$amount</td>";
            $html .= "<td>
                            <button class='btn btn-outline-warning btn-sm change-status' data-id='$id' data-type='transaction'>😫</button>
                        </td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $route_update = '/v1/change-transaction-status';
        $route_update_wallet = '/v1/update-wallet';
        $succeed_button = "<button class=\"btn btn-outline-success btn-sm\">👍</button>";
        $html .= "
            <script>
                $(document).on('click','.change-status',function (){
                    let item_id = $(this).data('id');
                    let item = $(this);
                    alert('فعلاً کاری انجام نمیده');
                    return false;
                        $.ajax({
                            url: '$route_update',
                            data: {
                                id: item_id
                            },
                            dataType: 'json',
                            type: 'get',
                            beforeSend: function (){
                                console.log('part 1');
                            },
                            success: function(data){
                                item.closest('td').html('$succeed_button');

                                $('#update_balance').html(data['balance']);
                                $('#update_gift_balance').html(data['gift_balance']);
                                $('#update_new_balance').html(data['new_balance']);
                                $('#update_status').html(data['status']);
                                $('#save-wallet').attr('data-balance',data['new_balance_raw']);

                                if (data['is_equal']){
                                    $('#save-wallet').trigger('click');
                                    $('#save-wallet').slideUp();
                                } else {
                                    $('#save-wallet').slideDown();
                                }
                            }
                        });
                });

                $(document).on('click','#save-wallet',function (){
                    $.ajax({
                        url: '$route_update_wallet',
                        data: {
                            customer_id: $(this).attr('data-customer_id'),
                            balance: $(this).attr('data-balance')
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function(data){
                            location.reload();
                        }
                    });
                });
            </script>
        ";

        $html .= $list->links('pagination::bootstrap-4');

        echo $html;
    }

    public function checkProductStoreBalance()
    {

        $product_id = \request()->product;

        $product = DB::table('products')->where('id',$product_id)->first();

        $varieties = DB::table('varieties as v')
            ->leftJoin('attribute_variety as av','v.id','=','av.variety_id')
            ->where('product_id',$product_id)
            ->select(
                'v.id',
                'av.value as value',
            )
            ->groupBy('v.id')
            ->get();

        $html = $this->commonHtmlForReport();
        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";
        $html .= "<h4 class='text-center'>گزارش موجودی های تنوع های محصول $product->title</h4>";

        $html .= "<div style='display: flex; justify-content: space-around'>";
        //$width = 100/count($varieties);
        foreach ($varieties as $variety) {

            $store = DB::table('stores')->where('variety_id',$variety->id)->first();
            $storeTransactions = DB::table('store_transactions')
                ->where('store_id',$store->id)
                ->get();

            $html .= "<div style='border: 1px solid; flex: 1; padding: 10px'>";
            $html .= "<h5 style='text-align: center'>$variety->value | موجودی : $store->balance</h5>";
            $html .= "<hr>";

            $html .= "<table class='table table-hover table-striped'>";
            $html .= "<tr>";
            $html .= "<th>نوع تراکنش</th>";
            $html .= "<th>تعداد</th>";
            $html .= "<th>شرح</th>";
            $html .= "<th>تاریخ</th>";
            $html .= "<th>موجودی</th>";
            $html .= "</tr>";

            $balance = 0;

            foreach ($storeTransactions as $storeTransaction) {

                $type = $storeTransaction->type=='increment'?"<span>⬆️</span>":"<span>🔻</span>";
                if ($storeTransaction->type == 'increment') {
                    $balance = $balance + $storeTransaction->quantity;
                } else {
                    $balance = $balance - $storeTransaction->quantity;
                }
                $date = $storeTransaction->created_at;
                $persian_date = (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($date);
                $is_done = $balance==0?"<hr>تمام شد":'';

                $html .= "<tr>";
                $html .= "<th>$type</th>";
                $html .= "<th>$storeTransaction->quantity</th>";
                $html .= "<th>$storeTransaction->description</th>";
                $html .= "<th>$date <hr class='m-0'> $persian_date</th>";
                $html .= "<th>$balance $is_done</th>";
                $html .= "</tr>";
            }
            $html .= "</table>";

            $html .= "</div>";
        }
        $html .= "</div>";

        echo $html;
    }

    public function updateBirthDate()
    {
        $list = DB::table('customers')
            ->select(
                'id',
                'mobile',
                'first_name',
                'last_name',
                'birth_date',
            )
            ->whereNotNull('birth_date')
            ->where('birth_date','<','1500-01-01')
            ->paginate();

        $html = $this->commonHtmlForReport();
        $html .= "<style>.change-status{user-select: none; cursor: pointer}</style>";
        $html .= "<h4 class='text-center'>لیست تاریخ تولد های ثبت شده به شمسی در دیتابیس</h4>";

        $html .= "<table class='table table-hover table-striped'>";
        $html .= "<tr>";
        $html .= "<th>شناسه مشتری</th>";
        $html .= "<th>موبایل</th>";
        $html .= "<th>نام و نام خانوادگی</th>";
        $html .= "<th>تاریخ تولد ذخیره شده</th>";
        $html .= "<th>تاریخ تولد میلادی (جهت ذخیره)</th>";
        $html .= "<th>عملیات</th>";
        $html .= "</tr>";

        foreach($list as $item){

            $id = $item->id;
            $mobile = $item->mobile;
            $full_name = $item->first_name . " " . $item->last_name;
            $birth_date = $item->birth_date;
//            $persian_birth_date = (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($birth_date);
            $miladi_birth_date = (new \Modules\Core\Helpers\Helpers)->convertShamsiToMiladi(str_replace('-','/',$birth_date));

            $html .= "<tr>";
            $html .= "<td>$id</td>";
            $html .= "<td>$mobile</td>";
            $html .= "<td>$full_name</td>";
            $html .= "<td><div style='direction: ltr'>$birth_date</div></td>";
            $html .= "<td><div style='direction: ltr'>$miladi_birth_date</div></td>";
            $html .= "<td>
                            <button class='btn btn-outline-warning btn-sm update_birth_date' data-id='$id' >💾</button>
                        </td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $route_update = '/v1/saveMiladiBirthDate';
        $html .= "
            <script>
                let count_done = 0;
                $(document).on('click','.update_birth_date',function (){
                    let item_id = $(this).data('id');
                    let item = $(this);
                    $.ajax({
                        url: '$route_update',
                        data: {
                            id: item_id
                        },
                        dataType: 'json',
                        type: 'get',
                        beforeSend: function (){
                            //console.log('part 1');
                        },
                        success: function(data){
                            if (data['done']){
                                item.closest('td').html('ذخیره شد');
                                count_done++;
                            }
                        }
                    });
                });

                $(document).ready(function(){
                    // Get all buttons on the page
                    let buttons = $('.update_birth_date');

                    // Define a function to trigger click event on a button
                    function triggerClick(button) {
                      button.click();
                    }

                    // Loop through each button and trigger click event in a queue
                    buttons.each(function(index, button) {
                      setTimeout(function() {
                        triggerClick($(button));
                      }, index * 2000); // Delay each click by 1 second (1000 milliseconds)
                    });

                    let interval = setInterval(function(){
                        if (count_done == 15){
                            clearInterval(interval);
                            location.reload();
                        }
                    },1000)
                });
            </script>
        ";

        $html .= $list->links('pagination::bootstrap-4');

        echo $html;
    }

    public function saveMiladiBirthDate(){

        $id = \request()->id;
        $customer = Customer::find($id);

        $customer->birth_date = (new \Modules\Core\Helpers\Helpers)->convertShamsiToMiladi(str_replace('-','/',$customer->birth_date));;
        $customer->save();

        $data = [
            'done' => true,
        ];

        return response()->json($data);
    }

    public function fullCustomerReport()
    {

        (new \Modules\Core\Helpers\Helpers)->updateChargeTypeOfTransactions();

        $days = 35;
        $original_date = \Request()->date??date('Y-m-d');
        if (isset(\Request()->reCalculate)){
            DB::table('orders')
                ->whereDate('created_at',$original_date)
                ->update(['items_count' => null]);
        }
        (new \Modules\Core\Helpers\Helpers)->updateOrdersCalculateData();

        $originalDate = new DateTime($original_date);
        $dates = [$original_date];
        for ($i = 1 ; $i < $days ; $i++){
//            $dates[] = date("Y-m-d", strtotime( "-$i days" ));
            $modifiedDate = $originalDate->modify("-1 days");
            $dates[] = $modifiedDate->format('Y-m-d');
        }

        $dates = array_reverse($dates);

        $report_data = [];
        foreach ($dates as $date) {
            $total_paid_from_wallet = $this->getTotalPaidFromWallet($date);
            $total_wallet_deposit = $this->getTotalWalletDeposit($date);
            $total_wallet_deposit_by_admin = $this->getTotalWalletDepositByAdmin($date);
            $total_wallet_deposit_by_admin_gift = $this->getTotalWalletDepositByAdmin($date,null,2);
            $total_wallet_deposit_by_order_gift = $this->getTotalWalletDepositByAdmin($date,null,1);
            $total_wallet_return = $this->getTotalWalletDepositByAdmin($date,null,4);
            $total_wallet_withdraw_by_admin = ($this->getTotalWalletWithdraw($date)*(-1)) - $total_paid_from_wallet;
            $total_withdraws_paid = $this->getPaidWithdraws($date);
            $total_wallet_balance = $this->getTotalWallet($date);

            $report_data[] = [
                'persian_date' => (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($date),
                'original_date' => $date,
                'total_paid_from_wallet' => $total_paid_from_wallet,
                'total_wallet_balance' => $total_wallet_balance,
                'total_wallet_deposit' => $total_wallet_deposit,
                'total_wallet_deposit_by_admin' => $total_wallet_deposit_by_admin,
                'total_wallet_deposit_by_admin_gift' => $total_wallet_deposit_by_admin_gift,
                'total_wallet_deposit_by_order_gift' => $total_wallet_deposit_by_order_gift,
                'total_wallet_return' => $total_wallet_return,
                'total_wallet_withdraw_by_admin' => $total_wallet_withdraw_by_admin,
                'total_withdraws_paid' => $total_withdraws_paid,
            ];
        }

        $html = $this->commonHtmlForReport($original_date);

        $persian_date = convertMiladiToShamsiWithoutTime($original_date);
        $html .= "<h4 class='text-center'>گزارش تراکنش های کیف پول $days روز اخیر (با توجه به تاریخ انتخابی)  <div class='inline-date'>$persian_date</div></h4>";

        $html1 = "<table class='table table-hover table-striped'>";

        $html1 .= "<tr>";
        $html1 .= "<th>تاریخ</th>";
        $html1 .= "<th>شارژ کیف پول اصلی <br> (بانک)</th>";
        $html1 .= "<th>شارژ کیف پول اصلی <br> (ادمین)</th>";
        $html1 .= "<th>شارژ کیف پول اصلی <br> (هدیه ادمین)</th>";
        $html1 .= "<th>شارژ کیف پول هدیه <br> (هدیه خرید)</th>";
        $html1 .= "<th>لغو سفارش +  <br> لغو درخواست برداشت</th>";
        $html1 .= "<th>تسویه از کیف پول اصلی</th>";
        $html1 .= "<th>برداشت از کیف پول اصلی <br> (ادمین + درخواست برداشت)</th>";
        $html1 .= "<th>برداشت توسط ادمین <br> (تأیید درخواست)</th>";
        $html1 .= "<th>بالانس کیف پول</th>";
//        $html1 .= "<th>بالانس روزانه</th>";
        $html1 .= "</tr>";

        foreach($report_data as $item){

            $persian_date = $item['persian_date'];
            $original_date = $item['original_date'];
            $total_paid_from_wallet = $item['total_paid_from_wallet'];
            $total_wallet_deposit = $item['total_wallet_deposit'];
            $total_wallet_deposit_by_admin = $item['total_wallet_deposit_by_admin'];
            $total_wallet_deposit_by_admin_gift = $item['total_wallet_deposit_by_admin_gift'];
            $total_wallet_deposit_by_order_gift = $item['total_wallet_deposit_by_order_gift'];
            $total_wallet_return = $item['total_wallet_return'];
            $total_wallet_withdraw_by_admin = $item['total_wallet_withdraw_by_admin'] - $item['total_withdraws_paid'];
            $total_withdraws_paid = $item['total_withdraws_paid'];
            $total_wallet_balance = $item['total_wallet_balance'];

//            $daily_wallet_balance = $total_wallet_deposit - $total_paid_from_wallet - $total_withdraws_paid;

            $total_paid_from_wallet = number_format($total_paid_from_wallet, 0 , '.' , ',' );
            $total_wallet_deposit = number_format($total_wallet_deposit, 0 , '.' , ',' );
            $total_wallet_deposit_by_admin = number_format($total_wallet_deposit_by_admin, 0 , '.' , ',' );
            $total_wallet_deposit_by_admin_gift = number_format($total_wallet_deposit_by_admin_gift, 0 , '.' , ',' );
            $total_wallet_deposit_by_order_gift = number_format($total_wallet_deposit_by_order_gift, 0 , '.' , ',' );
            $total_wallet_return = number_format($total_wallet_return, 0 , '.' , ',' );
            $total_wallet_withdraw_by_admin = number_format($total_wallet_withdraw_by_admin, 0 , '.' , ',' );
            $total_withdraws_paid = number_format($total_withdraws_paid, 0 , '.' , ',' );
//            $daily_wallet_balance = number_format($daily_wallet_balance, 0 , '.' , ',' );
            $total_wallet_balance = number_format($total_wallet_balance, 0 , '.' , ',' );

            $html1 .= "<td>$persian_date | $original_date</td>";
            $html1 .= "<td style='background: #c1f5c1'>$total_wallet_deposit</td>";
            $html1 .= "<td style='background: #c1f5c1'>$total_wallet_deposit_by_admin</td>";
            $html1 .= "<td style='background: #c1f5c1'>$total_wallet_deposit_by_admin_gift</td>";
            $html1 .= "<td style='background: #c1f5c1'>$total_wallet_deposit_by_order_gift</td>";
            $html1 .= "<td style='background: #c1f5c1'>$total_wallet_return</td>";
            $html1 .= "<td style='background: #f5c1c1'>$total_paid_from_wallet</td>";
            $html1 .= "<td style='background: #f5c1c1'>$total_wallet_withdraw_by_admin</td>";
            $html1 .= "<td style='background: #f5c1c1'>$total_withdraws_paid</td>";
            $html1 .= "<td>$total_wallet_balance</td>";
//            $html1 .= "<td>$daily_wallet_balance</td>";
            $html1 .= "</tr>";
        }

        $html1 .= "</table>";

        $html .= "<hr>";
        $html .= $html1;

        echo "<div class='container-fluid'>";
        echo $html;
        echo "</div>";
    }











    // came from vendor ================================================================================================
    public function __construct()
    {
        $this->reportService = new ReportService(\request('mode', 'online'));
    }

    public function chartType1(Request $request)
    {
        $chartService = App::make(ChartService::class, ['reportService' => $this->reportService]);
        $method = match (Route::getCurrentRoute()->getName()) {
            'admin.reports.chart1' => 'salesAmountByDate',
            'admin.reports.chart2' => 'ordersStatusByDate',
        };
        $data = $chartService->getChart($method, $request->input('type'));

        return response()->success('', compact('data'));
    }

    public function products()
    {
        // $reports = $this->reportService->productReport();
        // $reports = ProductReport::query()->groupBy('id')
        // ->addSelect([
        //     '*',
        //     'total_sale' => DB::raw('SUM(IF(amount < 0, -(amount * quantity), amount * quantity))
        //      AS total_sale'),
        //     'sell_quantity' => DB::raw('SUM(quantity) AS sell_quantity'),])
        //     ->filters()->get();
        $reports = ProductReport::query()
        ->selectRaw('*')
        ->selectRaw('SUM(IF(amount < 0, -(amount * quantity), amount * quantity)) AS total_sale')
        ->selectRaw('SUM(quantity) AS sell_quantity')
        ->groupBy('id')
        ->filters()
        ->paginate(50);
        // $sumTotalSale = 0;
        // $sumSellQuantity = 0;
        // foreach ($reports as $report) {
        //     $sumTotalSale += $report->total_sale;
        //     $sumSellQuantity += $report->sell_quantity;
        // }

        if (\request()->header('accept') == 'x-xlsx') {
            return Excel::download(new ProductReportExport($reports),
                __FUNCTION__.'-' . now()->toDateString() . '.xlsx');
        }

        if (request()->header('Accept') == 'application/json') {
            return response()->success('', [
                'reports' => $reports,
                // 'sum_total_sale' => $sumTotalSale,
                // 'sum_sell_quantity' => $sumSellQuantity
            ]);
        }
        return view('report::admin.products', compact('reports'));
    }

    /* یک محصول میدی و میاد تنوع هاشو لیست میکنه */
    public function varieties()
    {
        /** @var Product $product */
        $product = Product::findOrFail(\request('product_id'));
        if ($product->hasFakeVariety()) {
            return response()->success(['no_variety' => true]);
        }
        $varietyReports = [];
        $sumTotal = 0;
        $sumQuantity = 0;

        foreach ($product->varieties as $variety) {
            $varietyReport = [];
            $varietyReport['variety'] = $variety;

            $orderItems1 = collect();
            $orderItems2 = collect();
            $orderItems = collect();
            if (in_array(request('report_mini_product'), [0, 2])) {
                $orderItems1 = OrderItem::query()->whereHas('order', fn($q) => $q->success())
                    ->where('variety_id', $variety->id)->filters()->active()->get();
                $orderItems = $orderItems1;
            }
            if (in_array(request('report_mini_product'), [1, 2])) {
                $orderItems2 = MiniOrderItemReport::query()->whereHas('miniOrder')
                    ->where('variety_id', $variety->id)->filters()->get();
                $orderItems = $orderItems2;
            }
            if (request('report_mini_product') == 2) {
                $orderItems = collect(array_merge($orderItems1->toArray(), $orderItems2->toArray()));
            }

            $varietyReport['total'] = $orderItems->sum('amount');
            $varietyReport['quantity'] = $orderItems->sum('quantity');
            $sumTotal += $varietyReport['total'];
            $sumQuantity += $varietyReport['quantity'];
            $varietyReports[] = $varietyReport;
        }


        return response()->success('', [
            'variety_reports' => $varietyReports,
            'no_variety' => false,
            'sum_quantity' => $sumQuantity,
            'sum_total' => $sumTotal
        ]);
    }

    /* لیست تمامی تنوع ها */
    public function varietiesFilterPage(){
        return view('report::admin.varieties-filter');
    }
    public function varietiesFilter(){
        $reports = VarietyReport::with('product')->filters()->groupBy('id')->paginate(25);
            dd($reports);
        return view('report::admin.varieties', compact('reports'));
    }
    public function varietiesList()
    {
        $reports = VarietyReport::withTrashed()->with(['product','attributes'])->groupBy('id')->latest('id')->paginate(30);
        // $reports = $this->reportService->varietyReport();
        // $reports = $reports->get();

        if (\request()->header('accept') == 'x-xlsx') {
            return Excel::download(new VarietyReportExport($reports),
                __FUNCTION__.'-' . now()->toDateString() . '.xlsx');
        }
        if (request()->header('Accept') == 'application/json') {
            return response()->success('', compact('reports'));
        }
        return view('report::admin.varieties-list', compact('reports'));
    }

    public function orders(Request $request)
    {
        $type = $request->input('type');
        $data = match ($type) {
            'year' => $this->reportService->getByYear(\request('offset', 0), 'orderReportByDate'),
            'month' => $this->reportService->getByMonth((int)verta()->format('m'), \request('offset', 0), 'orderReportByDate'),
            'week' => $this->reportService->getByWeek('orderReportByDate'),
            default => null
        };


        return response()->success('', compact('data'));
    }

    public function stores()
    {
        $categoryIds = request('category_ids', false);
        $varietyId = request('variety_id', false);
        $productId = request('product_id', false);
        $startDate = request('start_date', false);
        $endDate = request('end_date', false);
        $orderDirection = request('direction', 'desc');
        $orderByColumn = request('column', 'id');
        $report = StoreReport::query()->with('variety')
            ->addSelect(['*',
                    'entrances' => DB::raw('SUM(entrances) as total_entrances'),
                    'output' => DB::raw('SUM(output) as total_output'),
                ]
            )->when($categoryIds, fn($q) => $q->whereHas('variety', fn($q) => $q->whereIn('id', $categoryIds))
            )->when($varietyId, fn($q) => $q->whereHas('variety', fn($q) => $q->whereKey($varietyId))
            )->when($productId, fn($q) => $q->whereHas('variety.product', fn($q) => $q->whereKey($productId))
            )->when($startDate || $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])
            )
            ->orderBy($orderByColumn, $orderDirection)
            ->groupBy('variety_id')
            ->paginateOrAll();


        return response()->success('گزارش انبار', compact('report'));
    }

    public function walletsBalance(): JsonResponse
    {
        $customer_id = request('customer_id', false);
        $startAmount = request('start_amount', 0);
        $endAmount = request('end_amount', 0);
        $direction = request('direction', 'desc');
        $wallet = Customer::query()
            ->when($customer_id, fn($q) => $q->where('id', $customer_id))
            ->when($startAmount || $endAmount, fn($q) => $q->whereHas('wallet', fn($q) => $q->whereBetween('balance', [$startAmount, $endAmount])))
            ->orderBy('id', $direction)
            ->paginateOrAll();

        return response()->success('', compact('wallet'));
    }

    public function prettyOrders(Request $request)
    {

        $reportService = app(ReportService::class);
        $reportQuery = $reportService->baseOrderReport()->latest('id')
            ->groupBy('id');

        $reports = $reportQuery->paginate();
//        return response()->success('', compact('reports'));

        return $this->prettyOrdersIndex($reports);
    }

    public function prettyOrdersIndex($reports = [])
    {
        $provinces = Province::query()->where('status',1)->select('id','name')->with('cities')->get();
        $max_invoice_amount = Order::max('total_invoices_amount');
        $max_item_count = Order::max('total_items_count');
        return view('report::admin.prettyOrder',compact('provinces','max_item_count','max_invoice_amount','reports'));
    }

    public function orderFilterHelper()
    {
        $max_total = OrderReport::getMaxTotalForFilter();
        $max_items_count = OrderReport::getMaxItemsCountForFilter();

        return response()->success('', compact('max_items_count', 'max_total'));
    }

}
