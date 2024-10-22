<?php

namespace Modules\Store\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Product\Entities\Variety;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Store\Entities\Store;
use Modules\Store\Http\Requests\Admin\StoreRequest;
//use Shetabit\Shopit\Modules\Store\Http\Controllers\Admin\StoreController as BaseStoreController;

class StoreController extends Controller
{

    public function store(StoreRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $store = Store::insertModelForIncreaseDecrease($request);
            $transaction = $store->transactions()->latest()->first();

            if (request()->header('Accept') == 'application/json') {
                return response()->success('محصول با موفقیت در انبار ثبت شد', compact('store', 'transaction'));
            }

            ActivityLogHelper::storeModel(' محصول در انبار ثبت شد', $store);

            return redirect()->back()->with('error', 'محصول با موفقیت در انبار ثبت شد');

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getTraceAsString());
            if (request()->header('Accept') == 'application/json') {
                return response()->error('مشکلی در برنامه رخ داده است:' . $exception->getMessage(), $exception->getTrace(), 500);
            }

            return redirect()->back()->with('error', 'مشکلی در برنامه رخ داده است');
        }
    }




    function loadVarieties(Request $request)
    {
        $varieties = Variety::query()
            ->where('product_id', $request->product_id)
            ->with('attributes', 'color', 'store')
            ->get()
            ->each(fn ($variety) => $variety->setAppends(['final_price']));

        return response()->json(compact('varieties'));
    }



    // came from vendor ================================================================================================
    public function index(): JsonResponse|View
    {
        $stores = Store::with('variety.attributes')->filters()->paginate();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست اقلام انبار', compact('stores'));
        }

        return view('store::admin.index', compact('stores'));
    }

    public function show(int $id): JsonResponse
    {
        $stores = Store::query()->with(['variety', 'transactions'])->findOrFail($id);

        return response()->success('', compact('stores'));
    }

}
