<?php

namespace Modules\Cart\Http\Controllers\All;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Classes\CartFromRequest;
use Modules\Home\Services\HomeService;

//use Shetabit\Shopit\Modules\Cart\Http\Controllers\All\CartController as BaseCartController;

class CartController extends Controller
{

    public function index(Request $request)
    {
        return response()->success('', CartFromRequest::checkCart($request));
    }

    public function getCarts(Request $request)
    {
        $homeService = new HomeService($request);

        return response()->success('', [
            'user' => $homeService->getUser(),
            'cart_request' => $homeService->getCartFromRequest()
        ]);
    }
}
