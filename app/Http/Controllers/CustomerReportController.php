<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Helpers\Helpers;
use Modules\Report\Exports\CustomerReportExport;

class CustomerReportController extends Controller
{
    public function index()
    {
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
            ->whereBetween('customer_id',[1000,10000])
            ->whereIn('status',$this->getStatusesForReport())
            ->whereNull('parent_id')
            ->latest('created_at')
            ->groupBy('customer_id')->get();


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

        $arrayList = [];
        foreach($list as $item){
            $newArray = [];
            foreach ($item as $key => $value) {
                $newArray[$key] = $value;
            }
            $arrayList[] = $newArray;
        }


        DB::table('customer_report')->insert($arrayList);
        dd($arrayList);

    }

    function getStatusesForReport(): array
    {
        return ['new','delivered', 'in_progress'];
    }

    public function add()
    {
        Schema::create('customer_report', function (Blueprint $table) {


            $table->unsignedBigInteger('id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('orders_count')->nullable();
            $table->unsignedBigInteger('items_count')->nullable();
            $table->unsignedBigInteger('total_order_amount')->nullable();
            $table->string('mobile')->nullable();
            $table->unsignedBigInteger('last_order_code')->nullable();
            $table->string('last_order_date')->nullable();
            $table->string('last_order_fee')->nullable();
            $table->string('last_order_month')->nullable();
            $table->string('last_order_year')->nullable();
            $table->string('full_name')->nullable();
            $table->string('gift_wallet_amount')->nullable();

        });
        dd('MIGRATION DONE');
    }


    public function get_excel()
    {
        $list = DB::table('customer_report')->get();
        return Excel::download(new CustomerReportExport($list),
            __FUNCTION__.'-' . now()->toDateString() . '.xlsx');



    }
}
