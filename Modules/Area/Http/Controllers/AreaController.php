<?php

namespace Modules\Area\Http\Controllers;

use Modules\Area\Entities\Province;
//use Shetabit\Shopit\Modules\Area\Http\Controllers\AreaController as BaseAreaController;

class AreaController
{
    public function index()
    {
        $provinces = Province::query()->active()->select('id', 'name')
            ->with(['cities' => function($query) {
                $query->select('id', 'name', 'province_id')->active();
            }])->latest()->get();

        return response()->success('', compact('provinces'));
    }
}
