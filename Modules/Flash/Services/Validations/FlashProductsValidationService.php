<?php

namespace Modules\Flash\Services\Validations;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Entities\Product;
use Shetabit\Shopit\Modules\Flash\Services\Validations\FlashProductsValidationService as BaseFlashProductsValidationService;

class FlashProductsValidationService /*extends BaseFlashProductsValidationService*/
{

    public function checkMaxQuantityProduct($currentProductModel, $productFromRequest)
    {
        if (!$this->id) {
            return;
        }
        $flashProduct = DB::table('flash_product')->where('flash_id', $this->id)
            ->where('product_id', $currentProductModel->id)->first();


        if ($flashProduct && ($currentProductModel->total_quantity + $flashProduct->sales_count) < $productFromRequest['quantity']) {

            $productFromRequest['quantity'] = $currentProductModel->total_quantity;
        }
    }






    // came from vendor ================================================================================================
    protected array $products = [];
    public array $productModels = [];

    protected int|null $id;

    public function __construct($products, $id = null)
    {
        $this->products = $products;
        $this->id = $id;
        $this->checkProducts();
    }

    public function checkProducts()
    {

        $productArray = $this->products ?: false;

        if ($productArray) {
            $productIds = collect($productArray)->pluck('id')->toArray();
            $productModels = Product::query()->with(
                ['varieties','varieties.store'])->whereIn('id', $productIds)
                ->orderByRaw('FIELD(`id`, ' . implode(", ", $productIds) . ')')->get();

            if (count($productArray) != $productModels->count()) {
                throw Helpers::makeValidationException('تعداد مشخصات وارد شده نامعتبر است');
            }

            foreach ($productArray as $key => $productFromRequest) {
                $currentProductModel = $productModels[$key];
                $this->checkMaxQuantityProduct($currentProductModel, $productFromRequest);
                $this->checkNotExistAnotherFlashProduct($currentProductModel);
            }
        }
    }


    public function checkNotExistAnotherFlashProduct($currentProductModel)
    {
        $today = today()->toDateString();
        $existProductActiveFlash = $currentProductModel->flashes()
            ->when($this->id, function (Builder $query) {
                return $query->where('flashes.id', '!=', $this->id);
            })
            ->whereDate('flashes.start_date', '<=', $today)
            ->whereDate('flashes.end_date', '>=', $today)
            ->where('flashes.status', '=', 1)
            ->count();

        if ($existProductActiveFlash > 0) {
            throw Helpers::makeValidationException(
                'محصول ' . $currentProductModel->title . ' در یک فروش ویژه فعال دیگری ثبت شده است. ',
                'product_exist_another_flash'
            );
        }
    }



}
