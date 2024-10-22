<?php

namespace Modules\Permission\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Permission\Http\Controllers\Admin\PermissionController as BasePermissionController;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Core\Entities\Permission;
use Modules\Core\Helpers\Helpers;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::query()->get()->groupBy('guard_name');

        return response()->success('لیست مجوز ها', compact('permissions'));
    }
}
