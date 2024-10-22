<?php

namespace Modules\Cart\Classes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Services\WarningMessageCartService;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
//use Shetabit\Shopit\Modules\Cart\Classes\CartFromRequest as BaseCartFromRequest;


class CartFromRequest
{
    /**
     * @var $varieties Collection
     */
    public $varieties;

    public function __construct(public $varietyIds, public $prices, public $quantities){}

    public function check()
    {
        $this->varieties = $varieties = Variety::query()->with(['product.unit', 'product.varieties.attributes', 'attributes', 'color'])
            ->withCommonRelations()->whereIn('id', $this->varietyIds)
            ->orderByRaw('FIELD(`id`, ' . implode(", ", $this->varietyIds) . ')')
            ->get();
        foreach ($varieties as $variety) {
            /** @var Product $product */
            $product = $variety->product;
            $product->giveVarieties = true;
        }
        $notInDbIds = $this->checkVarietyIsInDB();
        $warnings = [];
        foreach ($notInDbIds as $notInDbId) {
            $warnings[] = [
                'cart_id' => array_search($notInDbId, $this->varietyIds),
                'type' => 'unavailable'
            ];
        }
        $warningMessageService = new WarningMessageCartService();
        $c = 0;
        foreach ($varieties as $variety) {
            $cartId = array_search($variety->id, $this->varietyIds); // مجازی همون ایندکس
            $warningMessageService
                ->checkCartFromRequest($variety, $this->prices[$c], $this->quantities[$c], $cartId);
            $c++;
        }

        return [ ...$warnings, ...$warningMessageService->warning];
    }

    protected function checkVarietyIsInDB()
    {
        $notInDbVarieties = [];
        $varietyIds = $this->varietyIds;
        foreach ($varietyIds as $varietyId) {
            if (!$this->varieties->where('id', '=', $varietyId)->count()) {
                $notInDbVarieties[] = $varietyId;
            }
        }

        return $notInDbVarieties;
    }

    /**
     * Can call this method in controllers
     */
    #[ArrayShape(['warnings' => "array", 'varieties' => "\Illuminate\Support\Collection"])]
    public static function checkCart(Request $request)
    {
        $request->validate([
            'variety_ids' => 'nullable|string',
            'prices' => 'nullable|string',
            'quantities' => 'nullable|string'
        ]);
        if (!$request->filled('variety_ids') || !$request->filled('prices') || !$request->filled('quantities')) {
            return [
                'warnings' => [],
                'varieties' => collect()
            ];
        }
        $varietyIds = explode(',', $request->input('variety_ids'));
        $prices = explode(',', $request->input('prices'));
        $quantities = explode(',', $request->input('quantities'));
        $cartFromRequest = new \Shetabit\Shopit\Modules\Cart\Classes\CartFromRequest($varietyIds, $prices, $quantities);

        $warnings = $cartFromRequest->check();
        $varieties = $cartFromRequest->varieties;

        return [
            'warnings' => $warnings,
            'varieties' => $varieties
        ];
    }

    public static function addToCartFromRequest(Request $request)
    {
        $request->validate([
            'variety_ids' => 'nullable|string',
            'prices' => 'nullable|string',
            'quantities' => 'nullable|string'
        ]);
        if (!$request->filled('variety_ids') || !$request->filled('prices') || !$request->filled('quantities')) {
            return;
        }
        $varietyIds = explode(',', $request->input('variety_ids'));
        $prices = explode(',', $request->input('prices'));
        $quantities = collect(explode(',', $request->input('quantities')));


        /**
         * @var $customer Customer
         */
        $customer = \Auth::user();
        // پاک کردن تنوع هایی که الان تو کوکی اش هست و توی دیتابیس هم وجود دارد
        $customer->carts()->whereIn('variety_id', $varietyIds)->delete();

        $checkCart = static::checkCart($request);
        $warnings = $checkCart['warnings'];

        $arr = [];
        for ($i = 0; $i < count($varietyIds); $i++) {
            $realQuantity = $quantities[$i];
            foreach ($warnings as $warning) {
                if ($warning['cart_id'] == $i && $warning['type'] == 'quantity') {
                    $realQuantity -= $warning['diff_quantity'];
                }
            }
            $arr[] = [
                'variety_id' => $varietyIds[$i],
                'quantity' => $realQuantity
            ];
        }
        /**
         * @var $variety Variety
         */
        foreach ($checkCart['varieties'] as $variety) {
            $quantity = null;
            foreach ($arr as $item) {
                if ($item['variety_id'] == $variety->id) {
                    $quantity = $item['quantity'];
                    break;
                }
            }
            Cart::addToCart($quantity, $variety, $customer);
        }
        $customer->load('carts.variety.product');

        return $warnings;
    }
}
