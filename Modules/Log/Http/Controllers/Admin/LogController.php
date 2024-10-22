<?php

namespace Modules\Log\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Log\Http\Controllers\Admin\LogController as BaseLogController;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function logModelsList(): JsonResponse
    {
        $models = config('log.models');
        $causedModels = config('log.causedModels');

        return response()->success('لیست مدل ها', compact('models', 'causedModels'));
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $logName = \request('log_name', false);
        $event = \request('event_name', false);
        $model = \request('model', false);
        $causedBy = \request('causedBy', false);
        $causedById = \request('causedById', false);
        $caused = false;

        if ($causedBy && $causedById){
            $caused = $causedBy::query()->whereKey($causedById)->first();
        }

        $logs = Activity::query()
            ->when($model ,function ($query) use ($model){
                $query->where('subject_type', '=', $model);
            })->when($caused, function ($query) use ($caused){
                $query->causedBy($caused);
            })->when($event, function ($query) use ($event){
                $query->forEvent($event);
            })->when($logName, function ($query) use ($logName){
                $query->inLog($logName);
            })->latest('id');

        $logs = Helpers::paginateOrAll($logs);

        return response()->success('لاگ ها', compact('logs'));
    }

    public function show($id)
    {
        $log = Activity::query()->whereKey($id)->get();

        return response()->success('لاگ', compact('log'));
    }
}
