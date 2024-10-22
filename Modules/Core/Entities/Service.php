<?php

namespace Modules\Core\Entities;

//use Shetabit\Shopit\Modules\Core\Entities\Service as BaseService;

use Illuminate\Database\Eloquent\Model;

class Service
{
    protected $model;

    public function __construct()
    {
        $this->makeModel();
    }

    public function makeModel()
    {
        if (method_exists($this, 'model')) {
            $model = app($this->model());

            if ($model instanceof Model || $model instanceof \Illuminate\Foundation\Auth\User) {
                return $this->model = $model;
            } else {
                throw new \Exception("Class {$this->model()} must be an instance of Model");
            }
        }
    }
}
