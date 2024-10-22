<?php

namespace Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
//use Shetabit\Shopit\Modules\Report\Exports\ProductReportExport as BaseProductReportExport;


class ProductReportExport implements FromCollection
{
    public function __construct(protected $models){}

    public function collection()
    {
        $finalModels = [];
        $finalModels[] = [
            'شناسه',
            'عنوان',
            'تعداد فروش',
            'میزان فروش'
        ];
        foreach ($this->models as $model) {
            $item = [];
            $m = $model->toArray();
            $item[] = $m['id'];
            $item[] = $m['title'];
            $item[] = $m['sell_quantity'];
            $item[] = $m['total_sale'];
            $finalModels[] = $item;
        }
        $finalModels = collect([...$finalModels]);

        return $finalModels;
    }
}
