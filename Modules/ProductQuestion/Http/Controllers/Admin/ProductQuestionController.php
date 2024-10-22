<?php

namespace Modules\ProductQuestion\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Helpers\Helpers;
use Modules\ProductQuestion\Entities\ProductQuestion;

class ProductQuestionController extends Controller
{
/**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse|View
    {
        $status = \request('status', false);
        $id = \request('id');

        if ($id){
            $questions = ProductQuestion::query()->latest()->where('parent_id',$id);
        } else {
            $questions = ProductQuestion::query()->latest()->mainQuestion();
        }
        if ($status && Str::contains($status, ProductQuestion::getAvailableStatus())){
            $questions->status($status);
        }
        Helpers::applyFilters($questions);
        
        if (request()->header('Accept') == 'application/json') {
            $questions = Helpers::paginateOrAll($questions);
            return response()->success('لیست پرسش ها', compact('questions'));
		}
        $questions = $questions->paginate();
        $questionsCount = $questions->total();

        return view('productquestion::admin.index', compact('questions', 'questionsCount'));

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse|View
    {
        $question = ProductQuestion::query()->findOrFail($id);
        if (request()->header('Accept') == 'application/json') {

            return response()->success('', compact('question'));
		}

        $questions = ProductQuestion::query()  
            ->with('parent')  
            ->whereNotNull('parent_id')  
            ->where('parent_id', $id) 
            ->paginate();

        return view('productquestion::admin.show', compact('question', 'questions')); 
    }

    public function assignStatus(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(ProductQuestion::getAvailableStatus())],
            'id' => ['required', 'exists:product_questions,id']
        ]);

        $question = ProductQuestion::query()->findOrFail($request->id);

        $question->update(['status' => $request->status]);

        $status_text = ProductQuestion::$statusTexts[$request->status];

        ActivityLogHelper::updatedModel(' وضعیت پرسش تغییر کرد', $question);

        if (request()->header('Accept') == 'application/json') {
            return response()->success("وضعیت پرسش با موفقیت به {$status_text} تغییر کرد");
		}

        return redirect()->back()->with('success', "وضعیت پرسش با موفقیت به {$status_text} تغییر کرد");

    }

    public function answer(Request $request): JsonResponse|RedirectResponse
    {

        $request->validate([
            'parent_id' => ['required']
        ]);

        $productQuestion = new ProductQuestion();
        $productQuestion->fill($request->except('status'));
        $productQuestion->customer()->associate(null);
        $productQuestion->admin()->associate(auth()->user());
        $productQuestion->product()->associate($request->product_id);
        $productQuestion->creator_type = 'admin';
        $productQuestion->parent_id = $request->parent_id;
        $productQuestion->status = 'approved';
        $productQuestion->save();
        ActivityLogHelper::storeModel(' پاسخ به پرسش ثبت شد', $productQuestion);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('پاسخ به پرسش با موفقیت ثبت شد.', compact('productQuestion'));
		}

        return redirect()->back()->with('success', 'پاسخ به پرسش با موفقیت ثبت شد');

    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse|RedirectResponse
    {
        ProductQuestion::query()->where('parent_id',$id)->delete();

        $question = ProductQuestion::query()->findOrFail($id);
        $question->delete();
        ActivityLogHelper::deletedModel(' پرسش حذف شد', $question);


        if (request()->header('Accept') == 'application/json') {
            return response()->success('پرسش با موفقیت حذف شد', compact('question'));
		}

        return redirect()->back()->with('success', 'پرسش با موفقیت حذف شد');

    }
}
