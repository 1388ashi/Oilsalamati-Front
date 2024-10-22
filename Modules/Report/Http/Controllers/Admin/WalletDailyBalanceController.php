<?php

namespace Modules\Report\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Report\Entities\WalletDailyBalance;

class WalletDailyBalanceController extends Controller
{
    public function getWalletBalance()
    {
        $date = date('Y-m-d');
        $balance = (new ReportController)->getTotalWallet();
        $wdb = new WalletDailyBalance();
        $wdb->date = $date;
        $wdb->balance = $balance;
        $wdb->save();

        $balance = number_format($balance, 0 , '.' , ',' );

        return response()->success('موجودی کیف پول برای تاریخ ' . $date . ' با مبلغ ' . $balance . ' با موفقیت ثبت شد');
    }
    public function getWalletDailyBalanceList()
    {
        $list = WalletDailyBalance::query()
            ->select('date','balance')
            ->groupBy('date')
            ->orderBy('created_at','desc')
            ->get();

        // در صورتی که برای یک روز چند رکورد ثبت شده باشد از این کد کمک گرفته شود
//        $lastRecords = DB::table('table_name')
//            ->select(DB::raw('MAX(created_at) as max_created_at'))
//            ->groupBy(DB::raw('DATE(created_at)'))
//            ->get();
//
//        $results = collect();
//
//        foreach ($lastRecords as $record) {
//            $result = DB::table('table_name')
//                ->whereDate('created_at', date('Y-m-d', strtotime($record->max_created_at)))
//                ->orderByDesc('created_at')
//                ->first();
//            $results->push($result);
//        }

        return response()->success('لیست موجودی کیف پول به تفکیک تاریخ',compact('list'));
    }
}
