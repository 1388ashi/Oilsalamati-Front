<?php

namespace Modules\Setting\Http\Requests\Develop;

//use Shetabit\Shopit\Modules\Setting\Http\Requests\Develop\SettingUpdateRequest as BaseSettingUpdateRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;

class SettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $name = Helpers::getModelIdOnPut('setting');
        return [
            'group' => 'required|string',
            'label' => 'required|string',
            'name' => 'required|string|unique:settings,name,' . $name,
            'type' => 'required|string',
            'value' => 'nullable|string',
            'private' => 'required|boolean',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $private = ($this->private == "on") ? 1 : 0;
        $this->merge(['private' => $private]);
        $options = json_decode($this->input('options'));
        if (!is_array($options)) {
            $options = explode(',', $this->options);
            $this->merge(['options' => $options]);
        }

    }
}
