<?php

namespace Modules\Campaign\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Modules\Campaign\Entities\CampaignUserAnswer;

class CampaignReportExport implements FromCollection
{
    public function __construct(protected $models){}

    public function collection()
    {
        $finalModels = [];

        $finalModels[] = [
            'شماره',
            'سوال',
            'جواب',
        ];

        foreach ($this->models as $model) {
            $item = [];
            $m = $model->toArray();

            $item[] = $m['user']['mobile'];
            $item[] = $m['question']['question'];
            $item[] = CampaignUserAnswer::showAnswerByKey($m['question']['id'],$m['question']['type'],$m['answer']);
            $finalModels[] = $item;
        }

        $finalModels = collect([...$finalModels]);

        return $finalModels;
    }
}
