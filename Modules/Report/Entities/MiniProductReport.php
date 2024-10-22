<?php

namespace Modules\Report\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Category\Entities\Category;
//use Modules\Core\Entities\BaseModel;
//use Shetabit\Shopit\Modules\Report\Entities\MiniProductReport as BaseMiniProductReport;

class MiniProductReport extends Model implements \Modules\Report\Contracts\ProductReport
{
    protected $table = 'mini_product_reports_view';

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category',
            'product_id', 'category_id');
    }
}
