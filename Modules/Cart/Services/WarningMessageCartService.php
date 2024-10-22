<?php

namespace Modules\Cart\Services;

use Modules\Cart\Entities\Cart;
use Modules\Product\Entities\Variety;
//use Shetabit\Shopit\Modules\Cart\Services\WarningMessageCartService as BaseWarningMessageCartService;
use Closure;
use Prophecy\Call\Call;

class WarningMessageCartService
{

    private $carts;
    private Cart $cart;
    private Variety $variety;
    public array $warning = [];
    public array $deletedCartIds = [];


    public function __construct($carts = null)
    {
        $this->carts = $carts;
    }


    public function checkAll(): array
    {
        foreach ($this->carts as $cart) {
            $this->check($cart);
        }
        return ["warning" => $this->warning , "deleted_cart_ids" => $this->deletedCartIds];
    }

    public function check(Cart $cart)
    {
        $variety = $cart->variety;
        if (!$variety) {
            $cart->delete();
            return;
        }
        $this->checkPriceChanges($variety, $cart->price, $cart->id, function ($warning) use ($variety, $cart){
            $cart->setDiscountPrice($variety);
            $cart->setPrice($variety);
            $cart->save();
            $this->warning[] = $warning;
        });
        $this->checkQuantityChanges($variety, $cart->quantity, $cart->id, function ($warning) use ($cart) {
            $cart->quantity = $cart->quantity - $warning['diff_quantity'];
            $cart->save();
            $this->warning[] = $warning;
        });
        $this->checkVarietyStock($variety, $cart->id, function ($warning) use($cart) {
            $this->warning[] = $warning;
            $this->deletedCartIds[] = $cart->id;
            $cart->delete();
        });
    }

    /**
     * @param Variety $variety
     * @param $price
     * @param $quantity
     * @param $c آیدی فیک است که همان ایندکسی است که کاربر درخواست زده تا دوباره بهش بفهمونیم کدوم مشکل داره
     */
    public function checkCartFromRequest(Variety $variety, $price, $quantity, $c)
    {
        $this->checkPriceChanges($variety, $price, $c, function ($warning) {
            $this->warning[] = $warning;
        });
        $this->checkQuantityChanges($variety, $quantity, $c, function ($warning) {
            $this->warning[] = $warning;
        });
        $this->checkVarietyStock($variety, $c, function ($warning) {
            $this->warning[] = $warning;
        });
    }

    public function checkPriceChanges($variety, $cartPrice, $cartId, Closure $callback)
    {
        if ($variety->final_price['amount'] != $cartPrice){
            if ($variety->final_price['amount'] > $cartPrice){
                $priceChanges = $variety->final_price['amount'] - $cartPrice;
                $callback(["cart_id" => $cartId, "diff_price" =>  $priceChanges, 'type' => 'price']);
            }
            if ($variety->final_price['amount'] < $cartPrice){
                $priceChanges = $variety->final_price['amount'] - $cartPrice;
                $callback(["cart_id" => $cartId, "diff_price" => $priceChanges, 'type' => 'price']);
            }
        }
    }

    /**
     * @param $variety
     * @param $quantity
     * @param $callback
     * @return array
     */
    protected function checkQuantityChanges($variety, $quantity, $cartId, Closure $callback)
    {
        if ($variety->quantity != 0 && ($variety->quantity < $quantity)){
            $callback(["cart_id" => $cartId, "diff_quantity" => $quantity - $variety->quantity, "type" => "quantity"]);
        }
    }

    public function checkVarietyStock(Variety $variety, $cartId, Closure $callback)
    {
        if (!$variety->isAvailable()) {
            $callback(["cart_id" => $cartId, "type" => "unavailable"]);
        }
    }

}
