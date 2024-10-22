<?php

namespace Modules\ProductComment\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Helpers\Helpers;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\Product\Entities\Product;
use Modules\ProductComment\Entities\ProductComment;

class ProductCommentController extends Controller
{
    public function show($id): JsonResponse|View
    {
        $comment = ProductComment::query()->withCommonRelations()->findOrFail($id);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('', compact('comment'));
		}

        return view('productccomment::admin.show', compact('comment'));
    }

    public function assignStatus(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(ProductComment::getAvailableStatus())],
            'id' => ['required', 'exists:product_comments,id']
        ]);

        ProductComment::query()->findOrFail($request->id)->update(['status' => $request->status]);

        $get_score = Helpers::getCustomersClubScoreByKey('write_comment_on_bought_product');  // دریافت امتیازی که بابت ثبت کامنت روی محصول به مشتری تعلق می گیرد

        $product_comment = ProductComment::query()->findOrFail($request->id);
        $userBoughtProduct = Helpers::checkUserBoughtProduct($product_comment->creator_id, $product_comment->product_id);


        if ($get_score && $userBoughtProduct){
            // در صورتی که برای این مرحله امتیاز درنظر گرفته شده باشد و مشتری، محصول موردنظر را خریداری کرده باشد
            // ابتدا چک می شود که امتیاز داده شده یا نه و سپس درصورت ثبت نشده، امتیاز ثبت می گردد.

            $customer_club_score = CustomersClubScore::query()
                ->where('customer_id',$product_comment->creator_id)
                ->where('cause_id', $get_score->id)
                ->where('product_id', $product_comment->product_id)
                ->first();

            // در صوتی که امتیاز این مرحله وجود داشته باشد دوباره امتیاز داده نمی شود
            if (!$customer_club_score){

                $product = Product::find($product_comment->product_id)->title;

                $customer_club_score = new CustomersClubScore();
                $customer_club_score->customer_id = $product_comment->creator_id;
                $customer_club_score->product_id = $product_comment->product_id;
                $customer_club_score->cause_id = $get_score->id;
                $customer_club_score->cause_title = (new \Modules\Core\Helpers\Helpers)->generateCauseTitleByCauseId($get_score->id) . " ($product_comment->product_id - $product)";
                $customer_club_score->score_value = $get_score->score_value;
                $customer_club_score->bon_value = $get_score->bon_value;
                $customer_club_score->date = date('Y-m-d');
                $customer_club_score->status = 1;


                $customer_club_score->save();
            }
        }

        ActivityLogHelper::updatedModel(' وضعیت دیدگاه ویرایش شد', $product_comment);

        if (request()->header('Accept') == 'application/json') {
            return response()->success("وضعیت دیدگاه با موفقیت به {$request->status} تغییر کرد");
		}

        return redirect()->back()->with('success', "وضعیت دیدگاه با موفقیت به {$request->status} تغییر کرد");
    }





    // came from vendor ================================================================================================
    public function index(): JsonResponse|View
    {
        $comments = ProductComment::query()->filters()->latest('id')->paginate();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست دیدگاه ها', compact('comments'));
		}

        return view('productcomment::admin.index', compact('comments'));
    }
    public function destroy($id): JsonResponse|RedirectResponse
    {
        $comment = ProductComment::query()->findOrFail($id);
        $comment->delete();
        ActivityLogHelper::deletedModel(' دیدگاه حذف شد', $comment);


        if (request()->header('Accept') == 'application/json') {
            return response()->success('دیدگاه با موفقیت حذف شد', compact('comment'));
		}

        return redirect()->back()->with('success', 'دیدگاه با موفقیت حذف شد');
    }
}
