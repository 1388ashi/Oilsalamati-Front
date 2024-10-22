<?php

namespace Modules\Widget\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Widget\Http\Controllers\Admin\WidgetController as BaseWidgetController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WidgetController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->success('', []);
    }
}
