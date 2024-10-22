<?php

namespace Modules\Core\Entities;

//use Shetabit\Shopit\Modules\Core\Entities\Repository as BaseRepository;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class Repository
{
    public $model;
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    public function getFillable()
    {
        return $this->model->getFillable();
    }


    public function __call($func, $args)
    {
        $method = substr($func, 0, -2);

        if (substr($func, -2) === 'By') {
            $data = $this->model->where($args[0], $args[1])->latest('id');
            if (! empty($args[2])) {
//                dd($args);
                $data = $data->limit($args[2]);
            }

            return $data->$method();
        } else {
            throw new \Exception('Call to undefined method: ' . $method);
        }
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findBy($col, $value)
    {
        return $this->model->where($col, $value)->first();
    }

    public function getBy($col, $value, $limit = 10)
    {
        return $this->model->where($col, $value)->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function update($model, array $data)
    {
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }

        $model->save();

        return $model;
    }

    public function delete($model)
    {
        return $model->delete();
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

    public function paginate($limit = 10)
    {
        $request = request();

        return $this->model->orderBy('id', 'desc')
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->q, function ($query, $q) {
                $query->where('title', 'like', "%" . prettifyString($q) ."%");
            })
            ->paginate($limit);
    }
}
