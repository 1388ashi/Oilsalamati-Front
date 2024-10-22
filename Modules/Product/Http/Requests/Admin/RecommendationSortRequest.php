<?php

namespace Modules\Product\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Product\Http\Requests\Admin\RecommendationSortRequest as BaseRecommendationSortRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Entities\Recommendation;

class RecommendationSortRequest extends FormRequest
{

    public function rules()
    {
        $groups = app(CoreSettings::class)->get('product.recommendation.groups');

        return [
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:recommendations,id',
            'group' => 'required|string|'.Rule::in($groups)
        ];
    }

    protected function passedValidation()
    {
        $recommendationCount = Recommendation::query()->where('group', $this->group)->count();
        if ($recommendationCount != count($this->ids)){
            throw Helpers::makeValidationException('شماسه های وارد شده نامعتبر است');
        }
    }

    public function prepareForValidation()
    {
        $this->merge([
            'group' => $this->route('group')
        ]);
    }
}
