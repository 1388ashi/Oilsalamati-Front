<?php

namespace Modules\FAQ\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\FAQ\Http\Controllers\Admin\FAQController as BaseFAQController;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Helpers\Helpers;
use Modules\FAQ\Entities\FAQ;
use Modules\FAQ\Http\Requests\Admin\FAQStoreRequest;

class FAQController extends Controller
{
    public function index()
    {
        $fAQBuilder = FAQ::query()->orderBy('order', 'desc');
        Helpers::applyFilters($fAQBuilder);
        $faqs = $fAQBuilder->get();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('', compact('faqs'));
        }
        return view('faq::admin.index', compact('faqs'));
    }

    public function store(FAQStoreRequest $fAQStoreRequest)
    {
        $fAQ = new FAQ();
        $fAQ->fill($fAQStoreRequest->all());
        $fAQ->save();
        ActivityLogHelper::storeModel('سوالات متداول ثبت شد', $fAQ);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('سوال متداول با موفقیت ثبت شد', compact('fAQ'));
        }
        return redirect()->route('admin.faqs.index')
        ->with('success', 'سوال با موفقیت ثبت شد.');

    }
    public function sort(Request $request)
    {
      if (request()->header('Accept') == 'application/json') {
          FAQ::sort($request);

          return response()->success('مرتب سازی با موفقیت انجام شد');
        }
        FAQ::setNewOrder($request->faqs);

        return redirect()->route('admin.faqs.index')
        ->with('success', 'سوالات با موفقیت مرتب شد.');
    }

    public function update(FAQStoreRequest $fAQStoreRequest, $fAQId)
    {
        $fAQ = FAQ::findOrFail($fAQId);
        $fAQ->fill($fAQStoreRequest->all());
        $fAQ->save();
        ActivityLogHelper::updatedModel('سوالات متداول بروز شد', $fAQ);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('سوال متداول با موفقیت بروز شد', compact('fAQ'));
        }
        return redirect()->route('admin.faqs.index')
        ->with('success', 'سوال با موفقیت به روزرسانی شد.');
    }

    public function destroy($fAQId)
    {
        $fAQ = FAQ::findOrFail($fAQId);
        $fAQ->delete();
        ActivityLogHelper::deletedModel('سوالات متداول حذف شد', $fAQ);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('سوال متداول با موفقیت حذف شد', null);
        }
        return redirect()->route('admin.faqs.index')
        ->with('success', 'سوال با موفقیت حذف شد.');
    }
}
