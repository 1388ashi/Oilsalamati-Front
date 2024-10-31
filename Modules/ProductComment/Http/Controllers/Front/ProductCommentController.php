<?php

namespace Modules\ProductComment\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\ProductComment\Entities\ProductComment;
use Modules\ProductComment\ApiResources\ProductCommentResource;
//use Shetabit\Shopit\Modules\ProductComment\Http\Controllers\Front\ProductCommentController as BaseProductCommentController;

class ProductCommentController extends Controller
{
    public function show($productId): JsonResponse
    {
        $comment = ProductComment::query()
            ->where('product_id' , $productId)
            ->status(ProductComment::STATUS_APPROVED)
            ->orderBy('created_at','desc')
            ->orderBy('id','desc')
            ->filters()->paginateOrAll(200)->each(function ($item) {
                if($item->show_customer_name){
                    return $item->load('creator');
                }
            });

        return response()->json(['status' => 'success']);    
    }
}
