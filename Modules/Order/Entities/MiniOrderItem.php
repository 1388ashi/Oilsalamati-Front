<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Traits\HasMorphAuthors;
use Modules\Flash\Entities\Flash;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Store\Entities\Store;


class MiniOrderItem extends Model
{
    protected $fillable = ['description', 'discount_amount', 'status'];

    protected $with = ['product'];

    const TYPE_SELL = 'sell';
    const TYPE_REFUND = 'refund';


    public function miniOrder()
    {
        return $this->belongsTo(\Modules\Order\Entities\MiniOrder::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function flash()
    {
        return $this->belongsTo(Flash::class);
    }

    public function getVarietyAttribute($variety)
    {
        if (!$variety) {
            $variety = $this->variety()->first();
        }
        if ($variety) {
            $variety->append('title');
        }

        return $variety;
    }

    public static function getAvailableTypes()
    {
        return [self::TYPE_SELL, self::TYPE_REFUND];
    }

    public static function store(Variety $variety, $quantity, \Modules\Order\Entities\MiniOrder $miniOrder, $type, $amount)
    {
        /** @var Product $product */
        $product = $variety->product;
        /** @var Flash|null $activeFlash */
        $activeFlash = $product->activeFlash->first();
        $miniOrderItem = new static();
        $miniOrderItem->miniOrder()->associate($miniOrder);
        $miniOrderItem->quantity = $quantity;
        $miniOrderItem->variety()->associate($variety);
        $miniOrderItem->product()->associate($product);
        if ($activeFlash) {
            $miniOrderItem->flash()->associate($activeFlash);
        }
        $miniOrderItem->amount = $amount;
        $miniOrderItem->diff_amount_from_real = $amount - $variety->final_price['amount'];
        $miniOrderItem->discount_amount = $variety->final_price['discount_price'];
        $miniOrderItem->extra = collect([
            'attributes' => $variety->attributes()->get(['name', 'label', 'value']),
            'color' => $variety->color()->exists() ? $variety->color->name : null
        ])->toJson();
        $miniOrderItem->discount_amount = $variety->final_price['discount_price'];
        $miniOrderItem->type = $type;
        $miniOrderItem->save();

        $storeType = $type === \Modules\Order\Entities\MiniOrderItem::TYPE_SELL ? Store::TYPE_DECREMENT : Store::TYPE_INCREMENT;
        $msg = $storeType === Store::TYPE_INCREMENT ? 'اضافه کردن' : 'کم کردن';

        Store::insertModel((object)[
            'type' => $storeType,
            'description' => "{$msg} محصول {$variety->title}  به سفارش حضوری " . $miniOrderItem->id,
            'quantity' => $miniOrderItem->quantity,
            'mini_order_id' => $miniOrder->id,
            'variety_id' => $variety->id
        ]);
    }
}
