<?php

namespace Modules\Order\Http\Controllers\Front;


use Modules\Order\Entities\ShippingExcel;
use Shetabit\Shopit\Modules\Core\Http\Controllers\BaseController;

class ShippingExcelController extends BaseController
{
    public function index()
    {
        $shippingExcels = ShippingExcel::latest('register_date')
            ->where('created_at', '>', now()->subMonths(1))
            ->SearchKeywords()
            ->get();

        return response()->success('', [
            'shipping_excels' => $shippingExcels
        ]);
    }
}
