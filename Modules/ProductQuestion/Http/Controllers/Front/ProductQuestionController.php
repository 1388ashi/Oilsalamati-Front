<?php

namespace Modules\ProductQuestion\Http\Controllers\Front;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Modules\ProductQuestion\ApiResources\ProductQuestionResource;
use Modules\ProductQuestion\Entities\ProductQuestion;
use Modules\ProductQuestion\Http\Requests\Customer\ProductQuestionStoreRequest;

class ProductQuestionController extends Controller
{
    public function show($productId): JsonResponse
    {
        $question = ProductQuestion::query()
            ->where('product_id' , $productId)
            ->status(ProductQuestion::STATUS_APPROVED)
            ->MainQuestion()
            ->with(['answers' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->filters()->get()->each(function ($item) {
//                $item->answers = [];
            });

        return response()->success('', [
            'question' => ProductQuestionResource::collection($question)
        ]);
    }
}
