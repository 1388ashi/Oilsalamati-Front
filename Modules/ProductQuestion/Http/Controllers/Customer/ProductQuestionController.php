<?php

namespace Modules\ProductQuestion\Http\Controllers\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Entities\Customer;
use Modules\ProductQuestion\Entities\ProductQuestion;
use Modules\ProductQuestion\Http\Requests\Customer\ProductQuestionStoreRequest;
class ProductQuestionController extends Controller
{
    private ?Customer $user;

    public function __construct()
    {
        $this->middleware(function ($request , $next){

            $this->user = auth()->user();

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $questions = $this->user->productQuestions()->with('product')/*->where('customer_id',auth()->user())*/->filters()->paginateOrAll();

        return response()->success('لیست پرسش ها', compact('questions'));
    }

    /**
     * Store a newly created resource in storage.
     * @param ProductQuestionStoreRequest $request
     * @param ProductQuestion $productQuestion
     * @return JsonResponse
     */
    public function store(ProductQuestionStoreRequest $request , ProductQuestion $productQuestion): JsonResponse
    {
        $productQuestion->fill($request->except('status'));
        $productQuestion->customer()->associate(auth()->user());
        $productQuestion->admin()->associate(null);
        $productQuestion->product()->associate($request->product_id);
        $productQuestion->creator_type = 'customer';
        $productQuestion->parent_id = $request->parent_id??null;
        $productQuestion->save();
        $answer_text =  $request->parent_id?'پاسخ به ':'';
        return response()->success($answer_text . 'پرسش با موفقیت ثبت شد.', compact('productQuestion'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $question = $this->user->productQuestions()->withCommonRelations()->findOrFail($id);

        return response()->success('', compact('question'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $question = $this->user->productQuestions()->findOrFail($id);
        $question->delete();

        return response()->success('پرسش با موفقیت حذف شد', compact('question'));
    }
}
