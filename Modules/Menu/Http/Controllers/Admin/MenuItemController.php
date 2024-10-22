<?php

namespace Modules\Menu\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Menu\Http\Controllers\Admin\MenuItemController as BaseMenuItemController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Link\Http\Controllers\Admin\LinkController;
use Modules\Menu\Entities\MenuGroup;
use Modules\Menu\Entities\MenuItem;
use Modules\Menu\Http\Requests\MenuSortRequest;
use Modules\Menu\Http\Requests\MenuStoreRequest;
use Modules\Menu\Http\Requests\MenuUpdateRequest;
use Modules\Widget\Classes\Widget;

class MenuItemController extends Controller
{
  public function groups()
  {
      $menuGroups = MenuGroup::all('id', 'title');
      $group_labels = $menuGroups->map(function ($menuGroup) {
          if (Lang::has('core::groups.' . $menuGroup->title)) {
              return trans('core::groups.' . $menuGroup->title);
          }
          return false;
      });

      if (request()->header('Accept') == 'application/json') {
        return response()->success('', ['groups' => MenuGroup::all('id', 'title'),
      'group_labels' => $group_labels]);
      }
      return view('menu::admin.groups', ['groups' => MenuGroup::all('id', 'title'),'group_labels' => $group_labels]);
    }

    public function index($groupId)
    {
        $linkableData = (new LinkController)->create();

        $dataArray = $linkableData->getData(true);

        if ($dataArray['success'] && isset($dataArray['data']['linkables'])) {
            $linkables = $dataArray['data']['linkables'];
        }
        $menu_items = MenuItem::where('parent_id', null)->where('group_id', $groupId)->ordered()->with('children')->get();

      if (request()->header('Accept') == 'application/json') {
        return response()->success('', ['menu_items' => $menu_items]);
      }
      return view('menu::admin.index', compact('menu_items','linkables'));
    }
    public function grandChildIndex($groupId,$id)
    {
        $linkableData = (new LinkController)->create();

        $dataArray = $linkableData->getData(true);

        if ($dataArray['success'] && isset($dataArray['data']['linkables'])) {
            $linkables = $dataArray['data']['linkables'];
        }
      $menu = MenuItem::find($id);
      $menu_items = MenuItem::where('parent_id',$menu->id)
      ->where('group_id', $groupId)->ordered()->with('children')->get();

      return view('menu::admin.indexgrandchild', compact('menu_items','menu','linkables'));
    }
    public function childIndex($groupId,$id)
    {
        $linkableData = (new LinkController)->create();

        $dataArray = $linkableData->getData(true);

        if ($dataArray['success'] && isset($dataArray['data']['linkables'])) {
            $linkables = $dataArray['data']['linkables'];
        }

        $menu = MenuItem::find($id);
        $menu_items = MenuItem::where('parent_id', $menu->id)
        ->where('group_id', $groupId)->ordered()->with('children')->get();

        return view('menu::admin.indexchild', compact('menu_items','menu','linkables'));
    }
    public function store(MenuStoreRequest $request)
    {
        $menu = new MenuItem();
        $menu->fill($request->all());
        $menu->save();
        ActivityLogHelper::storeModel('منو ثبت شد', $menu);
        if ($request->hasFile('icon')) {
            $menu->addIcon($request->file('icon'));
        }
        $menu->refresh();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('منو با موفقیت اضافه شد', compact('menu'));
        }

        if ($menu->parent_id) {
          return redirect()->route('admin.menu.childIndex',[$menu->group_id,$menu->parent_id])
          ->with('success', 'منو با موفقیت ثبت شد.');
        }else{
          return redirect()->route('admin.menu.index',$menu->group_id)
          ->with('success', 'منو با موفقیت اضافه شد.');
        }
    }
    public function update(MenuUpdateRequest $request, MenuItem $menuItem)
    {
        if ($request->linkable_id) {
            $menuItem->link = null;
        }
      $request->all();
        $menuItem->update($request->all());
        if ($request->hasFile('icon')) {
            $menuItem->addIcon($request->file('icon'));
        }
        ActivityLogHelper::updatedModel('منو بروز شد', $menuItem);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('منو با موفقیت بروز شد', ['menu_item' => $menuItem]);
        }

        if ($menuItem->parent_id) {
          return redirect()->route('admin.menu.childIndex',[$menuItem->group_id,$menuItem->parent_id])
          ->with('success', 'منو با موفقیت ثبت شد.');
        }else{
          return redirect()->route('admin.menu.index',$request->group_id)
          ->with('success', 'منو با موفقیت به روزرسانی شد.');
        }
    }
    public function sort(Request $request, $id_item, $menu_item = null)
    {
        $orderIds = $request->orders;
        $items = MenuItem::whereIn('id', $orderIds)->get();

        $itemMap = $items->keyBy('id');

        foreach ($orderIds as $index => $id) {
            if (isset($itemMap[$id])) {
                $item = $itemMap[$id];
                $item->order = $index + 1;
                $item->save();
            }
        }

        if (request()->header('Accept') == 'application/json') {
          return response()->success('مرتب سازی با موفقیت انجام شد');
        }
        if ($menu_item) {
            $menu_item = MenuItem::find($menu_item);

            return redirect()->route('admin.menu.childIndex',[$id_item,$menu_item])
              ->with('success', 'منو با موفقیت مرتب سازی شد.');
        } else {
          return redirect()->route('admin.menu.index', ['id' => $id_item])
              ->with('success', 'منو با موفقیت مرتب سازی شد.');
        }
    }

    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuItem->delete();
        ActivityLogHelper::deletedModel('منو حذف شد', $menuItem);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('منو با موفقیت حذف شد', ['menu_item' => $menuItem]);
        }
        return redirect()->route('admin.menu.index',$menuItem->group_id)
        ->with('success', 'منو با موفقیت حذف شد.');
    }
}
