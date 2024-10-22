<?php

namespace Modules\Campaign\Http\Requests\Admin\CampaignUser;

use Illuminate\Foundation\Http\FormRequest;

class CampaignUserUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            //
        ];
    }


    public function authorize()
    {
        return true;
    }
}
