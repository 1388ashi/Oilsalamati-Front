<?php

namespace Modules\Campaign\Http\Requests\Admin\CampaignQuestion;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Campaign\Entities\Campaign;

class CampaignQuestionUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'question' => 'required|string|min:1',
            'type' => 'required|in:checkbox,options,text',
            'data' => 'nullable',
            'campaign_id' => '|exists:campaigns,id',
            'order' => 'nullable',
            'parent_id' => 'nullable|in:campaign_questions',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->type == 'checkbox' || $this->type == 'options' && isset($this->data)){
            $this->merge([
                'data' => json_encode($this->data),
            ]);
        }
    }

    public function authorize()
    {
        return true;
    }
}
