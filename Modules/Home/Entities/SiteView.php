<?php

namespace Modules\Home\Entities;

//use Shetabit\Shopit\Modules\Home\Entities\SiteView as BaseSiteView;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasDefaultFields;

class SiteView extends Model
{
    use HasDefaultFields;

     protected $connection='extra';

    public array $defaults = ['count' => 0];

    protected $fillable = ['count', 'hour', 'date'];

    public $timestamps = [];

    public static function store(): static
    {
        $model = static::getView();
        $model->increment('count');
        $model->save();

        return $model;
    }

    public static function getView(): static
    {
        return Cache::remember(now()->toDateString() . (int)now()->format('H'), 3600, function () {
            $model = static::query()->where([
                'date' => now()->toDateString(),
                'hour' => (int)now()->format('H')
            ])->first();

            if (!$model){
                $model = static::query()->create([
                    'date' => now()->toDateString(),
                    'hour' => (int)now()->format('H')
                ]);
            }

            return $model;
        });
    }

    public static function paginateArray($perPage, $array, $page = null, $options = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $array instanceof Collection ? $array : Collection::make($array);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

}
