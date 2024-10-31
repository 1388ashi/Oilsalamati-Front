<?php

namespace Modules\ProductComment\Http\Controllers\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Customer\Entities\Customer;
use Modules\ProductComment\Entities\ProductComment;
use Modules\ProductComment\Http\Requests\Customer\ProductCommentStoreRequest;
//use Shetabit\Shopit\Modules\ProductComment\Http\Controllers\Customer\ProductCommentController as BaseProductCommentController;

class ProductCommentController extends Controller
{
    public function store(ProductCommentStoreRequest $request , ProductComment $productComment): JsonResponse
    {
        $user = \Auth::guard('customer')->user();
        $productComment->fill($request->except('status'));
        $productComment->creator()->associate($user);
        $productComment->product()->associate($request->product_id);
        $productComment->save();

        return response()->json(['status' => 'success']);    
    }





    // came from vendor ================================================================================================
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
        $comments = $this->user->productComments()->with('product')->filters()->paginateOrAll();

        return response()->success('لیست دیدگاه ها', compact('comments'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $comment = $this->user->productComments()->withCommonRelations()->findOrFail($id);

        return response()->success('', compact('comment'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $comment = $this->user->productComments()->findOrFail($id);
        $comment->delete();

        return response()->success('دیدگاه با موفقیت حذف شد', compact('comment'));
    }

}
