<?php

namespace Modules\Home\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Classes\CoreSettings;
use Modules\Home\Entities\SiteView;
use Modules\Home\Services\BaseService;
use Modules\Home\Services\HomeService;

class HomeController extends Controller
{
    #درخواست های هوم جدا شدند
    public function index():JsonResponse
    {
        SiteView::store(); // count views
        $homeService = new HomeService();
        $response = $homeService->getHomeData();

        return response()->success(':)', compact('response'));
    }


    public function base():JsonResponse
    {
        $baseItems = app(\Modules\Core\Classes\CoreSettings::class)->get(key: 'home.base');
        $baseRouteService = new BaseService();
        $response = $baseRouteService->getBaseRouteCacheData($baseItems);


        return response()->success(':)', compact('response'));
    }

    public function get_user(): JsonResponse
    {
        $baseRouteService = new BaseService();
        $user = $baseRouteService->getUser();
        (new \Modules\CustomersClub\Http\Controllers\Admin\CustomersClubController)->setDailyLoginScore();
        return response()->success('کاربر', compact('user'));
    }


    public function item($itemName)
    {
        $coreSetting = app(CoreSettings::class);
        $homeItems = $coreSetting->get('home.front');

        if (!key_exists($itemName, $homeItems))
            return response()->error('آیتم در صفحه اصلی موجود نیست');

        $homeService = new HomeService();
        $response = $homeService->getHomeDataItem($itemName);
        return response()->success('', compact('response'));
    }
}
