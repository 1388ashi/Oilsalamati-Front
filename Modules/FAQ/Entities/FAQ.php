<?php

namespace Modules\FAQ\Entities;

//use Shetabit\Shopit\Modules\FAQ\Entities\FAQ as BaseFAQ;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasDefaultFields;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class FAQ extends Model implements Sortable
{
    use HasDefaultFields, SortableTrait;

    protected $defaults = [
        'order' => 1
    ];

    protected $fillable = [
        'question',
        'answer',
        'status'
    ];
    public $sortable = [
      'order_column_name' => 'order',
      'sort_when_creating' => true,
    ];
    protected $table = 'f_a_qs';

    public static function booted()
    {
        Helpers::clearCacheInBooted(static::class , 'home_f_a_qs');
    }

    public static function sort(Request $request)
    {
        $order = 999999;
        if (count($request->ids) != \Modules\FAQ\Entities\FAQ::query()->count()){
            return response()->error('مشخصه های وارد شده نامعتبر است');
        }
        foreach ($request->ids as $id) {
            $faq = \Shetabit\Shopit\Modules\FAQ\Entities\FAQ::query()->find($id);
            $faq->order = $order--;
            $faq->save();
        }

        Cache::forget('home_f_a_qs');
    }
}
