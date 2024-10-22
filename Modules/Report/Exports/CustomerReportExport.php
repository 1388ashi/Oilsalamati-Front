<?php

namespace Modules\Report\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
//use Shetabit\Shopit\Modules\Report\Exports\CustomerReportExport as BaseCustomerReportExport;

class CustomerReportExport implements FromCollection
{
    public function collection()
    {
        $finalModels = [];
        $finalModels[] = [
            'شماره موبایل',
            'نام و نام خانوادگی',
            'تعداد سفارشات',
            'میزان خرید کل',
            'آخرین ثبت سفارش',
            'شماره آخرین فاکتور مشتری',
            'مبلغ کل آخرین فاکتور مشتری',
            'ماه آخرین سفارش مشتری',
            'سال آخرین سفارش مشتری',
            'موجودی هدیه کیف پول',
        ];
        foreach ($this->models as $model) {
            $item = [];
//            $m = $model->toArray();
            $item[] = $model->mobile;
            $item[] = $model->full_name;
            $item[] = $model->orders_count;
            $item[] = $model->total_order_amount;
            $item[] = $model->last_order_date;
            $item[] = $model->last_order_code;
            $item[] = $model->last_order_fee;
            $item[] = $model->last_order_month;
            $item[] = $model->last_order_year;
            $item[] = $model->gift_wallet_amount;
//            $item[] = $m['customer']['mobile'];
//            $item[] = $m['real_full_name'];
//            $item[] = $m['orders_count'];
//            $item[] = $m['_total'];
//            $item[] = $m['last_order_date'];
//            $item[] = $m['id'];
//            $item[] = $m['total'];
//            $item[] = $m['last_order_mounth'];

            $finalModels[] = $item;
        }
        $finalModels = collect([...$finalModels]);

        return $finalModels;
    }

    public function __construct(protected $models){}

}
