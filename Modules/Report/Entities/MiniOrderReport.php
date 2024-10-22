<?php

namespace Modules\Report\Entities;


//class MiniOrderReport extends \Shetabit\Shopit\Modules\Order\Entities\MiniOrderReport

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;

class MiniOrderReport extends Model
{
    protected $table = 'mini_order_reports_view';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }
}
