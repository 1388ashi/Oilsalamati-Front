<?php

namespace Modules\Campaign\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasDefaultFields;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CampaignQuestion extends Model implements Sortable
{
    use HasDefaultFields, SortableTrait;
    protected $fillable = [
        'question','type','data',
        'campaign_id','order','parent_id',
    ];
    protected $defaults = ['order' => 1];
    public $sortable = ['order_column_name' => 'order', 'sort_when_creating' => true];
    protected $hidden = [
        'created_at','updated_at','order'
    ];

    #TODO : IMPLEMENT ORDER ON QUESTIONS


    public function scopeActive($query)
    {
        return $query->where('status',1);
    }

    public function isDeletable(): bool
    {
        return true;
    }


    public function scopeSearchKeywords($query)
    {
        return $query->when(request()->filled('keyword'), function ($q) {
            $q->where('question', 'LIKE', '%' . \request('keyword') . '%')
                ->orWhere('data','LIKE','%'.\request('keyword').'%')
            ;
        });
    }

    public function scopeSearchBetweenTwoDate($query)
    {
        $startDate = \request('from_date');
        $endDate = \request('to_date');

        return $query
            ->when($startDate & $endDate, function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('created_at', [$startDate, $endDate]);
            });
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

}
