<?php

namespace Modules\Menu\Http\Requests;

//use Shetabit\Shopit\Modules\Menu\Http\Requests\MenuSortRequest as BaseMenuSortRequest;

use Illuminate\Foundation\Http\FormRequest;

class MenuSortRequest extends FormRequest
{
    public function rules()
    {
        return [
            'menu_items' => 'required|array',
            'group_id' => 'required|exists:menu_groups,id'
        ];
    }
}
