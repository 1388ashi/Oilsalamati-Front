<?php

namespace Modules\Product\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Attribute\Entities\Attribute;
use Modules\Category\Entities\Category;
use Modules\Color\Entities\Color;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\Tag;
use Modules\Core\Helpers\Helpers;
use Modules\Product\Entities\Gift;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductSpecificationPivot;
use Modules\Product\Entities\Variety;
use Modules\Product\Http\Requests\Admin\ProductStoreRequest;
use Modules\Product\Http\Requests\Admin\ProductUpdateRequest;
use Modules\Product\Jobs\SendProductAvailableNotificationJob;
use Modules\Product\Jobs\SendProductDiscountNotificationJob;
use Modules\SizeChart\Entities\SizeChartType;
use Modules\Specification\Entities\Specification;
use Modules\Store\Entities\Store;
use Modules\Store\Services\StoreBalanceService;
use Modules\Unit\Entities\Unit;
use Shetabit\Shopit\Modules\Product\Exports\ProductExport;
use Throwable;
use Exception;

//use Shetabit\Shopit\Modules\Product\Http\Controllers\Admin\ProductController as BaseProductController;

class ProductController extends Controller
{

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $product->load([
            'specifications.pivot.specificationValues','specifications.pivot.specificationValue',
            'varieties.attributes.pivot.attributeValue',
        ]);
        //appends
        $units = Unit::latest('id')->get();
        $categories = Category::query()
        ->with('attributes.values', 'brands', 'specifications.values')
        ->with(['children' => function ($query) {
            $query->with('attributes.values', 'brands', 'specifications.values');
        }])
        ->with(['specifications.pivot.specificationValues','specifications.pivot.specificationValue'/* => function ($query) {
            $query->with('values');
            $query->latest('order');
        }*/])->parents()->orderBy('priority')
        ->get();
        $tags = Tag::query()->latest('id')->get();
    //    dd($product->specifications[3]->pivot->specificationValues[0]['value']);

        $product_categories = $product->categories->pluck('id')->toArray();

        $specification_ids=ProductSpecificationPivot::query()->where('product_id', $product->id)->pluck('specification_id')->toArray();
        if ($specification_ids) {
        $specifications = Specification::query()->whereIn('id', $specification_ids)->get();
        }

        $specifications = Specification::active()->where('public', 1)->with('values')->latest('order')->get();

    //    dd($product->specifications->where('id',7)->toArray());
    //    dd($specifications[4]->toArray());
    //    dd($product->specifications[3]->pivot->specificationValues[0]->value); //its ok for multiple select
    //    dd($product->specifications[1]->pivot->specificationValue->value); // this is ok for select

        $colors = Color::latest('id')->get();
        $attributes = Attribute::query()->latest('id')->get();

        $testString = "hello";


        return view('product::admin.products.edit', compact(
            'product','units','categories','tags',
            'specifications','colors','attributes', 'testString'
        ));
    }
    public function update(Request $request, Product $product)
    {

        $oldVarietiesDiscount = 0;
        foreach ($product->varieties()->get() as $variety){
            $oldVarietiesDiscount = $oldVarietiesDiscount + $variety->discount;
        }

        try {
            DB::beginTransaction();
            $oldStatus = $product->status;
            $oldProductDiscount = $product->discount;
            $product->fill($request->product);
            $product->checkStatusChanges($request->product);
            $product->unit()->associate($request->product['unit_id']);
            $product->brand()->associate($request->product['brand_id']);
            $product->save();
            $product->syncTags($request->product['tags']);
            $product->updateImages($request->product['images'] ?? []);

            $product->assignSpecifications($request->product);
            $product->assignSizeChart($request->product);
            /**
             * Insert Product Variety
             * Varieties are created with the products
             * @see Product method storeVarietyf
             */
            $product->assignVariety($request->product, true);
            $product->assignGifts($request->product);

            if (!empty($request->product['categories'])) {
                $product->categories()->sync($request->product['categories']);
            }

            if (($request->product['listen_charge'])
                && ($oldStatus != Product::STATUS_AVAILABLE)
                && ($product->status == Product::STATUS_AVAILABLE)
            ){
                SendProductAvailableNotificationJob::dispatch($product);
            }

            $requestVarietiesDiscount = 0;
            foreach ($request->product['varieties'] as $item) {
                $requestVarietiesDiscount = $requestVarietiesDiscount + $item['discount'];
            }

            if ((!($oldProductDiscount || $oldVarietiesDiscount) )
                &&($request->product['discount'] || $requestVarietiesDiscount )
            )
            {
                SendProductDiscountNotificationJob::dispatch($product);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getTraceAsString());
            return response()->error(' مشکلی در بروزرسانی محصول به وجود آمده :   ' . $e->getMessage());
        }

        $product->loadCommonRelations();

        return response()->success('محصول با موفقیت بروزرسانی شد', compact('product'));
    }







    // came from vendor ================================================================================================

    public function index(): JsonResponse|View
    {
        $products = Product::with('categories')->orderBy('created_at','DESC');
        if ($temp = Helpers::hasCustomSearchBy('category_id')) {
            $products->whereHas('categories', function($item) use ($temp){
                $item->where('id', $temp)->orWhere('parent_id', $temp);
            });
        }

        $products = $products->Filters()
            ->SortByCategory()
            ->Approved_at()
            ->paginate();
            $statusCounts = Product::getStatusCounts();
            $categories = Category::query()->latest()->get();
            $statuses = Product::getAvailableStatuses();

        return view('product::admin.products.index', compact(
            'products', 'statusCounts','categories',
            'statuses'
        ));

    }
    public function search(Request $request)
    {
        $q = \request('q');
        if(empty($q)){
            return response()->error('ورودی نامعتبر است');
        }
        $products = Product::query()
            ->select('id', 'title', 'discount_type', 'discount')
            ->with(['varieties' => function($query){
                $query->with(['store', 'attributes', 'color']);
            }]);
        if (is_numeric($q)){
            $products->orWhere('id', $q);
        }
        $products->orWhere('title', 'LIKE', '%'.$q.'%');

        $products_count = $products->count();
        $products = $products->take(10)->get();

        return response()->success('', compact('products', 'products_count'));
    }


  public function create()
  {
    $categories = Category::query()->select('id', 'title')->with('attributes')->get();
    $tags = Tag::query()->latest('id')->get();
    $units = Unit::query()->select('id', 'name')->latest('id')->get();
    $colors = Color::query()->select('id', 'name', 'code')->latest('id')->get();
    $attributes = Attribute::query()->select('id', 'name')->with('values')->get();
    $specifications = Specification::active()->where('public', 1)->with('values')->latest('order')->get();

    return view('product::admin.products.create', compact('categories', 'tags', 'units', 'colors', 'attributes','specifications'));
  }

    public function create_options()
    {
        $categories = Category::query()
            ->with('attributes.values', 'brands', 'specifications.values')
            ->with(['children' => function ($query) {
                $query->with('attributes.values', 'brands', 'specifications.values');
            }])
            ->with(['specifications' => function ($query) {
                $query->with('values');
                $query->latest('order');
            }])->parents()->orderBy('priority')
            ->get();
        $units = Unit::active()->get(['id', 'name']);
        $tags = Tag::get(['id', 'name']);
        $colors = Color::all();
        $public_specifications = Specification::active()->where('public', 1)->with('values')->latest('order')->get();
        $all_attributes = Attribute::with('values')->get();
        if (app(CoreSettings::class)->get('size_chart.type')) {
            $size_chart_types = SizeChartType::query()->filters()->latest()->get();
        } else {
            $size_chart_types = [];
        }
        $data = compact('categories', 'units', 'tags', 'colors',
            'public_specifications', 'all_attributes', 'size_chart_types');

        $coreSettings = app(CoreSettings::class);
        if ($coreSettings->get('product.gift.active')) {
            $data['gifts'] = Gift::all();
        }

//        return response()->success('', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param ProductStoreRequest $request
     * @param Product $product
     * @throws Throwable
     */
    public function store(ProductStoreRequest $request, Product $product)
    {
        $filteredSpecifications = collect($request->product['specifications'])->filter(function ($spec) {
            return isset($spec['value']);
        })->values()->all();

        $requestProduct['specifications'] = $filteredSpecifications;
        DB::beginTransaction();
        try {
            $product->fill($request->product);
            $product->checkStatusChanges($request->product);
            $product->unit()->associate($request->product['unit_id']);
            if (array_key_exists('product.brand_id', $request->all())) {
                $product->brand()->associate($request->product['brand_id']);
            }
            $product->save();
            if ($request->filled('product.images')) {
                $product->storeFiles($request->images, 'images');
            }
            $product->attachTags($request->product['tags']);


            $product->assignSpecifications( $requestProduct['specifications']);
            $product->assignSizeChart($request->product);
            /**
             * Insert Product Variety
             * Varieties are created with the products
             * @see Product method storeVariety
             */

            // $product->assignVariety($request->product);
            // $product->assignGifts($request->product);

            if (!empty($request->product['categories'])) {
                $product->categories()->attach($request->product['categories']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getTraceAsString());
            if (request()->header('Accept') == 'application/json') {
                return response()->error('مشکلی در ثبت محصول به وجود آمده: ' . $e->getMessage(), $e->getTrace());
            }
            return redirect()->back()->withInput()->with('error', 'مشکلی در ثبت محصول به وجود آمده');

        }

        // $product = $product->loadCommonRelations();
        /** بروزرسانی تاریخ  برای سایت مپ */
        $product->categories()->touch();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('محصول با موفقیت ایجاد شد', compact('product'));
        }
        return redirect()->back()->with('success', 'محصول با موفقیت ایجاد شد');

    }

    /**
     * Show the specified resource.
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $product = Product::where('id', $id)->with('varieties.attributes.values')->withCommonRelations()->firstOrFail();

        return response()->success('', compact('product'));
    }

    public function excel(Request $request, $id)
    {
        $product = Product::withCommonRelations()->with('varieties.product')->findOrFail($id);
        switch ($request->type) {
            case 1:
                return Excel::download((new ProductExport($product)),
                    'product-' . $id . '.xlsx');
        }
    }

    public function approved_product(int $id, string $type)
    {
        $product = Product::query()->findOrFail($id);
        if ($type == "approve") {
            $product->update(['approved_at' => Carbon::now()->toDateTimeString()]);

            return redirect()->back()->with('success','محصول با موفقیت تایید و قابل نمایش شد');
        }
        if ($type == "disapprove") {
            $product->update(['approved_at' => null]);

            return redirect()->back()->with('success','محصول با موفقیت لغو تایید و غیرقابل نمایش شد');
        }
        return redirect()->back();


    }

    // only available az tu create ordercontroller miad
    public function listProducts($onlyAvailable = false)
    {
        $products = Product::latest('id')->select('id', 'title', 'discount_type', 'discount')->with(['varieties' => function($query) use ($onlyAvailable) {
            if (!$onlyAvailable) {
                $query->without('media');
            }
            $query->with(['store', 'attributes', 'color']);
        }]);
        if ($onlyAvailable) {
            $products = $products->available()->get();
        } else {
            $products->without('media');
            $products = $products->get()->makeHidden('images');
            foreach ($products as $product) {
                /**
                 * @var $variety Variety
                 */
                foreach ($product->varieties as $variety) {
                    $variety->makeHidden(['images', 'product']);
                }
            }
        }

        return response()->success('', compact('products'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->success('محصول مورد نظر با موفقیت حذف شد', compact('product'));
    }

}
