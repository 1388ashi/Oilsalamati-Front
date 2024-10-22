<?php

namespace Modules\Core\Services\Cache;

use Illuminate\Support\Facades\Cache;

abstract class CacheServiceInterface
{
    public $cacheData;
    public static array $usedModelsInCache = [];

    abstract protected function constructNeedId():bool;
    abstract public function cacheCreator($model_id):void;
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    protected static function getCacheName($id=''):string {
        $classBaseName = class_basename(static::class);
        return $classBaseName.$id;
    }
    public static function forgetCache($model_id=null):void {
        $instance = new static($model_id);
        $instance->checkModelIdRequirement($model_id); /* it runs twice. no matter. */
        if ($instance->constructNeedId())
            Cache::forget(self::getCacheName($model_id));
        else Cache::forget(self::getCacheName());
    }

    public function __construct($model_id='')
    {
        $this->checkModelIdRequirement($model_id);
        $cacheName = self::getCacheName($model_id);
        if (Cache::has($cacheName))
            $this->cacheData = Cache::get($cacheName);
        else $this->cacheCreator($model_id);
    }

    private function checkModelIdRequirement($model_id):void {
        if ($this->constructNeedId() && ($model_id == '' || $model_id == null)) {
            $className = class_basename(static::class);
            throw new \InvalidArgumentException("$className class need id in construct function");
        }
    }

    public static function getUsedModelsInCache():array {
        return static::$usedModelsInCache;
    }
}
