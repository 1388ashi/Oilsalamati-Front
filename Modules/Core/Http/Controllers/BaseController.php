<?php

namespace Modules\Core\Http\Controllers;

//use Shetabit\Shopit\Modules\Core\Http\Controllers\BaseRouteController as BaseBaseController;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    public function run($job, $params)
    {
        return app()->make($job, $params)->handle();
    }
}
