<?php

namespace Modules\Color\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Exceptions\ModelCannotBeDeletedException;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasAuthors;
use Modules\Product\Entities\Variety;
//use Shetabit\Shopit\Modules\Color\Entities\Color as BaseColor;

class Color extends Model
{
    use HasAuthors;

    protected $hidden = ['created_at', 'updated_at','creator_id','updater_id'];

    protected static $commonRelations = [
        //'products'
    ];

    protected $fillable = [ 'name', 'code', 'status', 'creator_id', 'updater_id' ];


    public static function booted()
    {
        static::deleting(function (\Modules\Color\Entities\Color $color) {
            if ($color->varieties()->exists()) {
                throw new ModelCannotBeDeletedException('این رنگ قابل حذف نمی باشد');
            }
        });

        Helpers::clearCacheInBooted(static::class, 'home_color');
    }
    public function scopeFilters($query)
    {
        return $query
            ->when(request('name'), function($query){
                $query->where('name', 'like', "%". request('name') ."%");
            })
            ->when(request('code'), function($query){
                $query->where('code', request('code'));
            })
            ->when(filled(request('status')), function($query){
                if (request('status') != 'all') {
                    $query->where('status', request('status'));
                }
            });
    }

    public function varieties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Variety::class);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
