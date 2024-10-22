<?php

namespace Modules\Home\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Modules\Attribute\Entities\Attribute;
use Modules\Category\Entities\Category;
use Modules\Color\Entities\Color;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Cache\CacheServiceInterface;
use Modules\Core\Services\NotificationService;
use Modules\Menu\Entities\MenuItem;
use Modules\Setting\Entities\Setting;

class BaseService
{
    public function getBaseRouteCacheData($baseItems):array {
        if (Cache::has("BaseRouteData"))
            return Cache::get("BaseRouteData");
        // MENU =======================================================
        $BaseRouteData['menu'] = MenuItem::query()->orderBy('order', 'desc')
            ->isParent()->active()->with('children', 'group')->get()->groupBy('group.title')->toArray();
        // SETTINGS ===================================================
        $databaseSettings = Setting::query()->where('private', false)->get()->toArray();
        foreach ($databaseSettings as $databaseSetting) {
            $BaseRouteData['settings'][$databaseSetting['group']][$databaseSetting['name']] = $databaseSetting['value'];
        }
        // CATEGORIES =================================================
        $categories = Category::query()
            ->with('children')
            ->active()
            //                ->orderBy('home_order','ASC')
            ->orderBy('priority', 'DESC')
            ->parents()
            ->get()
            ->toArray();
        foreach ($categories as $i => $category) {
            foreach ($category['children'] as $j => $child) {
                $categories[$i]['children'][$j]['image'] = Helpers::getImages("Category", $categories[$i]['children'][$j]['id'], true);
            }
        }
        $BaseRouteData['categories'] = $categories;
        // SIZE_VALUES =================================================
        $sizeAttribute = Attribute::whereName('size')->select('id')->first();
        if (!$sizeAttribute) {
            $BaseRouteData['size_values'] = [];
        } else {
            $BaseRouteData['size_values'] = [
                'id' => $sizeAttribute->id,
                'values' => $sizeAttribute->values()->select(['id', 'value'])->get()->toArray()
            ];
        }
        // SPECIAL_CATEGORIES =================================================
        $BaseRouteData['special_categories'] = Category::query()->take(10)->special()->active()->latest()->get()->toArray();
        // COLORS =================================================
        $BaseRouteData['colors'] = Color::query()->select(['id', 'name', 'code'])->active()->get()->toArray();
        // put cache ====================================================
        $base_cache_time = app(CoreSettings::class)->get('home.base_cache_time') ?? 10;

        Cache::put("BaseRouteData",$BaseRouteData, $base_cache_time);
        return $BaseRouteData;
    }

    public function getUser(): array
    {
        //todo:fix checkCart
        // $cacheStructure['cartsRequest'] = CartFromRequest::checkCart($this->request);
        $user = \Auth::guard('customer-api')->user();
        $notificationService = $user == null ? null : new NotificationService($user);
        $user?->load('listenCharges');

        $carts = null;
        if ($user) {
            $carts = $user->carts;
            foreach ($carts ?? [] as $cart)
                $cart->getReadyForFront();
            $user->unsetRelation('carts');
        }

        return [
            'user' => ($user == null) ? false : $user,
            'device_token' => ($user == null) ? false : $user->currentAccessToken()->device_token,
            'login' => !(($user == null)),
            'carts' => $carts,
            'carts_showcase' => ($user == null) ? null : $user->get_carts_showcase($carts),
            'notifications' => ($user == null) ? null : [
                'items' => $notificationService->get(),
                'total_unread' => $notificationService->getTotalUnread()
            ]
        ];
    }

}
