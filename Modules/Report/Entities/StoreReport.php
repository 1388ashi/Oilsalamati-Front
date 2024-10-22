<?php

namespace Modules\Report\Entities;

//use Shetabit\Shopit\Modules\Report\Entities\StoreReport as BaseStoreReport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Product\Entities\Variety;
use Modules\Store\Entities\StoreTransaction;
//use Shetabit\Shopit\Modules\Core\Entities\BaseModel;

class StoreReport extends Model
{
    protected $table = 'store_reports_view';

    protected $appends = ['remaining_toman', 'sales_toman'];

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class, 'variety_id')->with('product');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StoreTransaction::class, 'store_id');
    }

    public function getRemainingTomanAttribute(): float|int
    {
        return ($this->total_entrances - $this->total_output) * ($this->variety ? $this->variety->final_price['amount'] : 0);
    }

    public function getSalesTomanAttribute(): float|int
    {
        return ($this->total_output) * ($this->variety ? $this->variety->final_price['amount'] : 0);
    }
}
