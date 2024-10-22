<?php

namespace Modules\FAQ\Http\Controllers\Front;

//use Shetabit\Shopit\Modules\FAQ\Http\Controllers\Front\FAQController as BaseFAQController;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\Helpers;
use Modules\FAQ\Entities\FAQ;
use Modules\FAQ\Http\Requests\Admin\FAQStoreRequest;

class FAQController extends Controller
{
    public function index()
    {
        $fAQBuilder = FAQ::query()->orderBy('order', 'asc');
        Helpers::applyFilters($fAQBuilder);
        $fAQs = Helpers::paginateOrAll($fAQBuilder);

        return response()->success('', compact('fAQs'));
    }

    public function show(FAQ $fAQ)
    {
        return response()->success('', compact('fAQ'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|unique:f_a_qs'
        ]);
        $request->merge([
            'answer' => 'نیازمند پاسخ'
        ]);
        $fAQ = new FAQ($request->all());
        $fAQ->save();

        return response()->success('سوال شما موفقیت ثبت شد', compact('fAQ'));
    }
}

