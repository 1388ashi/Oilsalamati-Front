<?php

namespace Modules\Page\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Page\Http\Controllers\Admin\PageController as BasePageController;

use App\Http\Controllers\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Helpers\Helpers;
use Modules\Page\Entities\Page;
use Modules\Page\Http\Requests\Admin\PageRequest;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest();
        $pages =  Helpers::paginateOrAll($pages);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('', compact('pages'));
        }
        return view('page::admin.index',compact('pages'));
    }
    public function create()
    {
        return view('page::admin.create');
    }

    public function show(Page $page)
    {
        return response()->success('', compact('page'));
    }

    public function store(PageRequest $request)
    {
        $page = Page::create($request->all());
        ActivityLogHelper::storeModel('صفحه ثبت شد', $page);

        return redirect()->route('admin.pages.index')
        ->with('success', 'صفحه با موفقیت ثبت شد.');
    }
    public function edit(Page $page)
    {
        return view('page::admin.edit',compact('page'));
    }
    public function update(PageRequest $request, Page $page)
    {
        $page->update($request->all());
        ActivityLogHelper::updatedModel('صفحه بروز شد', $page);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('صفحه با موفقیت بروز شد', compact('page'));
        }
        return redirect()->route('admin.pages.index')
        ->with('success',  'صفحه با موفقیت به روزرسانی شد.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        ActivityLogHelper::deletedModel('صفحه حذف شد', $page);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('صفحه با موفقیت حذف شد', null);
        }
        return redirect()->route('admin.pages.index')
        ->with('success', 'صفحه با موفقیت حذف شد.');
    }
}
