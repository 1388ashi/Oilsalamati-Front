<?php

namespace Modules\Report\Entities;

//class BothProductReport extends \Shetabit\Shopit\Modules\Report\Entities\BothProductReport

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Category\Entities\Category;
//use Modules\Core\Entities\BaseModel;

class BothProductReport extends Model
{
    protected $table = 'both_product_reports_view';

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category',
            'product_id', 'category_id');
    }
}
