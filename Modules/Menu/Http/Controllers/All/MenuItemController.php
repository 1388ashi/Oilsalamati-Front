<?php

namespace Modules\Menu\Http\Controllers\All;

//use Shetabit\Shopit\Modules\Menu\Http\Controllers\All\MenuItemController as BaseMenuItemController;

use App\Http\Controllers\Controller;
use Modules\Menu\Entities\MenuItem;

class MenuItemController extends Controller
{
    public function index($groupId)
    {
        $menuItems = MenuItem::where('group_id', $groupId)->active()
            ->where('parent_id', null)->orderBy('order', 'desc')->with('children')->get();

        return response()->success('', ['menu_items' => $menuItems]);
    }

    public function show($menuItemId)
    {
        $menuItem = MenuItem::active()->findOrFail($menuItemId);
        $menuItem->load('children');

        return response()->success('', ['menu_item' => $menuItem]);
    }
}
