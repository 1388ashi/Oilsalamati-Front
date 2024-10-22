<?php

namespace Modules\SizeChart\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\SizeChart\Http\Requests\Admin\StoreSizeChartRequest as BaseStoreSizeChartRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Modules\SizeChart\Entities\SizeChart;
use Modules\Core\Classes\CoreSettings;

class StoreSizeChartRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        $settings = app(CoreSettings::class);
        $haveType = $settings->get('size_chart.type')
            ? 'required|integer|exists:size_chart_types,id'
            : 'nullable|integer|exists:size_chart_types,id';

        return [
            'title' => 'required|unique:size_charts,title',
            'type_id'  => $haveType,
            'chart' => 'required|array|present',
            'chart.*' => 'required|array|present',
            'chart.*.*' => 'required|present',
        ];
    }

    protected function passedValidation()
    {

    }
}

