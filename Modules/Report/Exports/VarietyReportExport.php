<?php

namespace Modules\Report\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Shetabit\Shopit\Modules\Report\Exports\VarietyReportExport as BaseVarietyReportExport;

class VarietyReportExport implements FromCollection
{
    public function __construct(protected $models){}


    public function collection()
    {
        $finalModels = [];
        $finalModels[] = [
            'شناسه',
            'عنوان',
            'تعداد فروش',
            'میزان فروش',
            'فی محصول'
        ];
        foreach ($this->models as $model) {
            $item = [];
            if (gettype($model) == 'array'){
                $m = $model;
            } else {
                $m = $model->append('title')->toArray();
            }

            $item[] = $m['id'];
            $item[] = $m['title'];
            $item[] = $m['sell_quantity'];
            $item[] = $m['total_sale'];
            $item[] = $m['price'];
            $finalModels[] = $item;
        }
        $finalModels = collect([...$finalModels]);

        return $finalModels;
    }
}
