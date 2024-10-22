<?php

namespace Modules\Widget\Classes;

//use Shetabit\Shopit\Modules\Widget\Classes\Widget as BaseWidget;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
class Widget /*extends BaseWidget*/
{
    public static function applyWidgets(&$data)
    {
        $request = request();
        if ($request->input('is_widgeting')) {
            return;
        }
        if (!$request->filled('widgets')) {
            return;
        }
        $request->merge(['is_widgeting' => true, 'all' => 1]);
        $data['widgets'] = [];
        try {
            $widgets = json_decode($request->query('widgets'));
        } catch (\Exception $exception) {
            return;
        }

        // Remove filters
        for ($i = 1; $i < 99; $i++) {
            $request->merge([
                'search' . $i => null
            ]);
        }

        $user = \Auth::user() ? \Auth::user()::class : 'all';
        $configuredWidgets = config('widget.' . $user);
        /**
         * @var $widgets از درخواست
         */
        foreach ($widgets as $widget) {
            $params = [];
            if (str_contains($widget, ':')) {
                $temp = explode(':', $widget);
                $widget = $temp[0];
                $params = array_slice($temp, 1);
            }
            if (array_key_exists($widget, $configuredWidgets)) {
                $widgetData = static::getWidgetData($configuredWidgets[$widget][0], $configuredWidgets[$widget][1], $params);
                foreach ($widgetData as $key => $datum) {
                    $temp = $data['widgets'];
                    $temp[$key] = $datum;
                    $data['widgets'] = $temp;
                }
            }
        }
    }








    // came from vendor ================================================================================================
    static $logs = [];

    public static function log($data)
    {
        static::$logs[] = $data;
    }

    public static function getWidgetData($controller, $method, $params = [])
    {
        $data = app($controller)->{$method}(...$params);

        return $data->original['data'];
    }

    public static function appendRules(&$data) {
        if (app()->runningUnitTests()){
            return;
        }
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET' || request('is_widgeting')) {
            return;
        }
        $controller = new ReflectionClass(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[3]['class']);
        if (!$controller->isInstantiable()) {
            return;
        }
        $controller = new ReflectionClass(app(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[3]['class']));
        foreach (['store', 'update'] as $_r) {
            if ($controller->hasMethod($_r)) {
                $storeMethod = $controller->getMethod($_r);
                foreach ($storeMethod->getParameters() as $parameter) {
                    if ($parameter->hasType() && class_exists($parameter->getType())) {
                        $reflectionClass = new ReflectionClass($parameter->getType()->getName());
                        if (!$reflectionClass->isInstantiable()) {
                            continue;
                        }
                        $parameterType = new ($parameter->getType()->getName());
                        if ($parameterType instanceof \Illuminate\Foundation\Http\FormRequest) {
                            $data[$_r . '_rules'] = $parameterType->rules();
                        }
                    }
                }
            }
        }
    }
}
