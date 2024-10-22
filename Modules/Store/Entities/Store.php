<?php

namespace Modules\Store\Entities;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Entities\HasFilters;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Store\Jobs\SendProductUnavailableJob;
use Modules\Store\Jobs\ProductUnavailableNotificationJob;
//use Shetabit\Shopit\Modules\Store\Database\factories\StoreFactory;
//use Shetabit\Shopit\Modules\Store\Entities\Store as BaseStore;

class Store extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    public static function insertModelForIncreaseDecrease($request, $forced = false): ?Store
    {
        throw new \InvalidArgumentException("this is using of Store old method. function name is: ". __FUNCTION__);
        if ($request->has('type')){
            throw new Exception('خطا');
        }
        //variety
        if (!$request || (request('product.no_store_update') && !$forced)) {
            return null;
        }
        $variety = Variety::query()->find($request->variety_id);
        if (!$variety) {
            throw new Exception('به علت حذف یکی از تنوع ها امکان این عمل وجود ندارد');
        }

        $store = $variety->store()->exists() ? $variety->store : $variety->store();

        $type = 0 ;
        $quantity = 0;

        if($request->quantity < $store->balance){
            //250 => 200
            $type = self::TYPE_DECREMENT;
            $quantity = $store->balance - $request->quantity;
        }


        if($request->quantity > $store->balance){
            //200 => 250
            $type = self::TYPE_INCREMENT;
            $quantity = $request->quantity - $store->balance;
        }

        if ($type == 0){
            //
        }

        if (! $store->exists()) {
            $store = $store->create([
                'balance' => 0
            ]);
        }

        if ($type == self::TYPE_DECREMENT && $store->balance < $quantity){
            $variety->load('attributes');
            throw new Exception("موجودی محصول " . $variety->title ." کمتر از {$request->quantity} عدد است.", '422');
        }

        /** @var $store Store */
        $data = [
            "type" => $type,
            "description" => $request->description,
            "quantity" => $quantity,
            'order_id' => $request->order_id ?? null
        ];
        if (isset($request->mini_order_id) && $request->mini_order_id) {
            $data['mini_order_id'] = $request->mini_order_id;
        }
        $transaction = $store->transactions()->create($data);

        $method = $transaction->type; // increment , decrement
        $store->$method('balance', $transaction->quantity);
        //example $store->increment('balance', $transaction->quantity);
        //example $store->decrement('balance', $transaction->quantity);
        return $store;
    }

    protected static function booted()
    {
        static::updated(function (\Modules\Store\Entities\Store $store) {
            if ($store->balance == 0) {
                $siblingIds = $store->variety->product->varieties
                    ->where('variety_id', '!=', $store->variety_id)
                    ->pluck('id')
                    ->all();
                if (count($siblingIds) == 0 || Store::whereIn('variety_id', $siblingIds)->where('balance', '>', 0)->count() == 0) {
                    $product = $store->variety->product;
                    $product->update([
                        'status' => Product::STATUS_OUT_OF_STOCK,
                    ]);
                    ProductUnavailableNotificationJob::dispatch($product);

                    self::removeDiscount($store);
                }
            }

            if ($store->balance < 1 ){
                $product = $store->variety->product;
                $balance = $store->balance;
                $variety = $store->variety;

                self::removeDiscount($store);

                SendProductUnavailableJob::dispatch($product,$balance,$variety);
            }

            if ($store->isDirty('balance')) {
                \Cache::forget('variety-quantity-' . $store->variety->id);
            }
        });
    }

    #وقتی موجودی یک محصول صفر بشه،میاد و تخفیفش رو بر میداره

    public static function removeDiscount($store)
    {
        $store->variety->discount_type = null;
        $store->variety->discount = null;
        $store->variety->discount_until = null;
        $store->variety->save();

        $store->variety->product->discount_type = null;
        $store->variety->product->discount = null;
        $store->variety->product->discount_until = null;
        $store->variety->product->save();

        Log::warning('به دلیل اتمام موجودی تنوع، تخفیف محصول هم برداشته شد'."\n".'product_id : ' . $store->variety->product->id . "\n".'variety_id : ' . $store->variety->id);
    }








    // came from vendor ================================================================================================
    use /*HasCommonRelations,*/ HasFilters;

    const TYPE_INCREMENT = 'increment';
    const TYPE_DECREMENT = 'decrement';

    public static $commonRelations = [
        /*'variety'*/
    ];

    protected $fillable = [
        'balance',
        'variety_id'
    ];


    public static function getAvailableTypes()
    {
        return [self::TYPE_INCREMENT, self::TYPE_DECREMENT];
    }


    //Relations

    public function variety(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Variety::class, 'variety_id')->with('product');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreTransaction::class, 'store_id');
    }

    /**
     * @throws Exception
     */
    public static function insertModel($request, $forced = false): ?Store
    {
        throw new \InvalidArgumentException("this is using of Store old method. function name is: ". __FUNCTION__);
        if (!$request || (request('product.no_store_update') && !$forced)) {
            return null;
        }
        $variety = Variety::query()->find($request->variety_id);
        if (!$variety) {
            throw new Exception('به علت حذف یکی از تنوع ها امکان این عمل وجود ندارد');
        }
        $store = $variety->store()->exists() ? $variety->store : $variety->store();

        if (! $store->exists()) {
            $store = $store->create([
                'balance' => 0
            ]);
        }

        if ($request->type == self::TYPE_DECREMENT && $store->balance < $request->quantity){
            $variety->load('attributes');
            throw new Exception("موجودی محصول " . $variety->title ." کمتر از {$request->quantity} عدد است.", '422');
        }
        /** @var $store Store */
        $data = [
            "type" => $request->type,
            "description" => $request->description,
            "quantity" => $request->quantity,
            'order_id' => $request->order_id ?? null
        ];
        if (isset($request->mini_order_id) && $request->mini_order_id) {
            $data['mini_order_id'] = $request->mini_order_id;
        }
        $transaction = $store->transactions()->create($data);

        $method = $transaction->type; // increment , decrement
        $store->$method('balance', $transaction->quantity);
        //example $store->increment('balance', $transaction->quantity);
        //example $store->decrement('balance', $transaction->quantity);
        return $store;
    }
}
