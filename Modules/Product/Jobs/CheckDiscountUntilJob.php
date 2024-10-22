<?php

namespace Modules\Product\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;

class CheckDiscountUntilJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = now()->format('Y-m-d H:i');
        $appliedOnProductsCount = Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'discount_until', 'discount_type', 'discount'])
            ->whereNotNull('discount_until')
            ->where('discount_until','<=',$now)
            ->update([
                'discount_until' => null,
                'discount_type' => null,
                'discount' => null
            ]);
        Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'discount_until', 'discount_type', 'discount'])
            ->whereNull('discount_until')
            ->whereRaw(" (discount_type is not null or discount is not null) ")
            ->update([
                'discount_until' => null,
                'discount_type' => null,
                'discount' => null
            ]);

        Log::debug("checkDiscountJobCalled -- applied in products.count -->",[$appliedOnProductsCount]);


        $appliedOnVarietiesCount = Variety::query()
            ->withoutGlobalScopes()
            ->select(['id', 'discount_until', 'discount_type', 'discount'])
            ->whereNotNull('discount_until')
            ->where('discount_until','<=',$now)
            ->update([
                'discount_until' => null,
                'discount_type' => null,
                'discount' => null
            ]);
        Variety::query()
            ->withoutGlobalScopes()
            ->select(['id', 'discount_until', 'discount_type', 'discount'])
            ->whereNull('discount_until')
            ->whereRaw(" (discount_type is not null or discount is not null) ")
            ->update([
                'discount_until' => null,
                'discount_type' => null,
                'discount' => null
            ]);

        Log::debug("checkDiscountJobCalled -- applied in varieties.count -->",[$appliedOnVarietiesCount]);






//        $products = Product::query()
//            ->select(['id', 'discount_until', 'discount_type', 'discount'])
//            ->with(['varieties' => function ($query) {
//                $query->select(['id', 'discount_until', 'discount_type', 'discount']);
//                $query->whereNotNull('discount_until');
//            }])
//            ->whereNotNull('discount_until')
//            ->get();
//
//        Log::debug("checkDiscountJobCalled",[$products->count()]);
//        /** @var Product $product */
//        foreach ($products as $product) {
//
//            if (($product->discount_until) && ($product->discount_until <= $now)) {
//                // Log::debug("checkDiscountJobCalled => must deleted discount",[$product]);
//                Log::info("محصول با شناسه $product->id بررسی شد و تخفیف آن برداشته شد");
//                $product->discount = null;
//                $product->discount_type = null;
//                $product->discount_until = null;
//                $product->save();
//            }
//
//            $varieties = DB::table('varieties')->where('product_id',$product->id)->get();
//            foreach ($varieties as $variety) {
//                if ($variety->discount_until <= $now) {
//                    $v = Variety::find($variety->id);
//                    if ($v->discount){
//                        Log::info(" محصول با شناسه $product->id با شناسه تنوع $variety->id بررسی شد و تخفیف آن برداشته شد");
//                        $v->discount = null;
//                        $v->discount_type = null;
//                        $v->discount_until = null;
//                        $v->save();
//                    }
//                }
//            }
////            if ($product->varieties()->exists()) {
////                foreach ($product->varieties as $variety) {
////                    if ($variety->discount_until <= $now) {
////                        Log::info("تنوع محصول با شناسه $product->id با شناسه تنوع $variety->id بررسی شد و تخفیف آن برداشته شد");
////                        $variety->discount = null;
////                        $variety->discount_type = null;
////                        $variety->discount_until = null;
////                        $variety->save();
////                    }
////                }
////            }
//
//        }
//
//        $varieties = Variety::where('discount_until','<=',$now)->get();
//        foreach ($varieties as $variety) {
//            Log::info("تنوع با شناسه $variety->id بررسی شد و تخفیف آن برداشته شد");
//            $variety->discount = null;
//            $variety->discount_type = null;
//            $variety->discount_until = null;
//            $variety->save();
//        }
    }
}
