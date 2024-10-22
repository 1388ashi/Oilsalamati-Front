<?php

//require_once(base_path('vendor/shetabit/shopit/src/Modules/Core/macros.php'));



use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Entities\Admin;
use Modules\Widget\Classes\Widget;
use Shetabit\Shopit\Modules\Core\Exports\ModelExport;

ResponseFactory::macro('success', function($message, array $data = null, $httpCode = 200) {

    if (Auth::user() instanceof Admin && \request()->header('accept') == 'x-xlsx') {
        return Excel::download(new ModelExport(array_values($data)[0]),
            array_key_first($data).'-' . now()->toDateString() . '.xlsx');
    }

//    Widget::appendRules($data);
//    Widget::applyWidgets($data);

    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data,
//        'logs' => Widget::$logs
    ], $httpCode);
});

ResponseFactory::macro('error', function($message, $data = null, $httpCode = 400) {
    if ($httpCode == 422) {
        if (\request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message,
            ], $httpCode);
        }


        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $data,
//            'logs' => Widget::$logs
        ], $httpCode);
    }



    return response()->json([
        'success' => false,
        'message' => $message,
        'data' => $data,
//        'logs' => Widget::$logs
    ], $httpCode);
});

//\Route::macro('superGroup', function ($model, $middlewares = []) {
//    $this->prefix($model)->name($model . '.')->namespace(ucfirst($model));
//    if (empty($middlewares)) {
//        $middlewares[] = 'auth:' . $model . '-api';
//    }
//    $this->middleware($middlewares);
//
//    return $this;
//});
