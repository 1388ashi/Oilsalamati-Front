<?php

namespace Modules\CustomersClub\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Modules\Campaign\Entities\CampaignUserAnswer;

class UserLevelExport implements FromCollection
{
    public function __construct(protected $array){}

    public function collection()
    {
        $finalModels = [];

        $finalModels[] = [
            'موبایل',
            'نام و نام خانوادگی',
            'سطح',
            'امتیاز',
            'بن',
        ];

        foreach ($this->array as $item) {
            $finalModels[] = $item;
        }

        $finalModels = collect([...$finalModels]);

        return $finalModels;
    }
}
