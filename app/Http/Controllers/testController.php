<?php

namespace App\Http\Controllers;

use App\Jobs\testJob;
use Facade\Ignition\DumpRecorder\Dump;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Entities\Province;
use Modules\Blog\Entities\Post;
use Modules\Cart\Entities\Cart;
use Modules\Category\Entities\Category;
use Modules\Category\Services\CategoriesCollectionService;
use Modules\Core\Classes\CoreSettings;
use Modules\Flash\Services\FlashMediaService;
use Modules\Home\Services\HomeService;
use Modules\Order\Entities\OrderItem;
use Modules\Order\Entities\OrderStatusLog;
use Modules\Order\Entities\OrderUpdater;
use Modules\Order\Services\Order\OrderUpdaterService;
use Modules\Product\Entities\CategoryProductSort;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;
use Modules\Product\Services\ProductDetailsService;
use Modules\Product\Services\ProductsCollectionService;
use Modules\Product\Services\ProductSearchService;
use Modules\Report\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\Order;
use Modules\Setting\Entities\Setting;
use Modules\Shipping\Entities\Shipping;
use Modules\Specification\Entities\Specification;
use Modules\Store\Entities\Store;
use Modules\Store\Services\StoreBalanceService;
use Shetabit\Shopit\Modules\Core\Entities\User;
use Spatie\Activitylog\Models\Activity;


class testController extends Controller
{

    public function variety($type)
    {
        $product = null;
        switch ($type)
        {
            case 'nothing':
                $product = Product::find(156);
                break;
            case 'color':
                $product = Product::find(4796); /* just colors */
                break;
            case 'color_attribute':
                $product = Product::find(4632); /* colors one attribute */
                break;
            case 'one_attribute':
                $product = Product::find(4736); /* no color. single attribute */
                break;
            case 'two_attribute':
                $product = Product::find(133);
                break;
        }
        $product->loadVarietiesShowcase($product->varieties);
        return response()->success('', compact('product'));
    }

    public $array = [];
    public function index()
    {
        dd(ProductDetailsService::getUsedModelsInCache());
        dd(Schema::connection('mysql')->hasColumn('customers', 'mobile'));



        dd(class_basename($this).".".__function__ . " HERE");
//        $array1 = [2, 4, 6, 8, 10, 12];
//        $array2 = [1, 2, 3, 4, 5, 6];
//
//        dd(array_intersect($array1,$array2));




//        $product = Product::find(180); /* atlas */
//        $product = Product::find(4594); /* atlas */

//        dump('no color. single attribute');
//        $product = Product::find(4736); /* no color. single attribute */

        dump('colors one attribute');
        $product = Product::find(4632); /* colors one attribute */

//        dump('just colors');
//        $product = Product::find(4796); /* just colors */

//        dump('two attributes');
//        $product = Product::find(133);

//        dump('no colors no attribute');
//        $product = Product::find(156); /* just colors */

        $product->loadVarietiesShowcase($product->varieties);
        dd($product->varieties_showcase);
        dd(class_basename($this).".".__function__ . " HERE");
        dd(class_basename($this).".".__function__ . " HERE");
        foreach ($product->varieties as $variety)
            dump($variety->title);
        dd(__function__ . " HERE");
//        dd($product->varieties[0]->title);
        return response()->success('', compact('product'));
        dd(__function__ . " HERE");

        ProductDetailsService::forgetCache(12);
        ProductsCollectionService::forgetCache();
        dd(Shipping::query()->active()->get()->toArray());
        dd(Product::query()->count());
        $arr = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
        ];
        dd($arr['forth'] ?? PHP_INT_MAX);
        $arr = [1,2,3,4,5,6,7];
        dd(array_search(5, $arr));
        $products = (new ProductsCollectionService())->getProductsCollection();

        $products = $products->filter(function ($product) {
            if ($product->final_price['amount'] > 900000 && $product->final_price['amount'] < 1000000) return $product;
            return false;
        })->values();
        dd($products);

        dd($products->min('final_price.amount'));
        dd((new ProductsCollectionService())->getSortList('byCategory'));
        dd((new CategoriesCollectionService())->getAllProductsOfCategory(24));
        dd((new CategoriesCollectionService())->getAllProductsOfCategory(24));
        dd(__function__ . " HERE");
        $searchKeyWord = 'روغن آرگان';
        $result = (new ProductSearchService($searchKeyWord))->getProductIdsSearchResult();
        dd($result);

        dd((new ProductsCollectionService()));
        dd((new ProductDetailsService(101))->getSalesCount());
        $product = Product::find(101);
        $count = OrderItem::query()
            ->select(DB::raw('SUM(quantity) as sales_count'))
            ->where('product_id', 101)
            ->where('status',1)->first()->sales_count;
        dd($count);

        Request()->merge([
            'type' => 'month',
            'mode' => 'online',
            'offset_year' => 0,
            'month' => 6
        ]);
        (new ReportController())->chartType1Light();
        $products = Product::all();
        foreach ($products as $item) {
            dd($item->getFinalPriceAttribute()['amount']);
        }
        ActivityLogHelper::simple('read orders.index', 'read',Order::find(1006));
        /* @var $order Order */
        $order = Order::find(1006);
        $order->status = Order::STATUS_WAIT_FOR_PAYMENT;
        $order->save();
        ActivityLogHelper::updatedModel('order updated', $order);


        dd('end');

        $object = Activity::find(672);
        $object->delete();
        ActivityLogHelper::deletedModel('deleted successfully', $object);
        dd('end');

        $product = Product::findOrFail(182);
        $product->setAppends(['specifications_showcase']);
        return response()->success('', compact('product'));
        dd($product);
        $product->load(['specifications.pivot.specificationValues','specifications.pivot.specificationValue']);
        $productDetailsCache['specifications'] = [];

        foreach ($product->specifications as $specification) {
            switch ($specification->type)
            {
                case Specification::TYPE_TEXT:
                    $productDetailsCache['specifications'][$specification->label] = $specification->pivot->value;
                    break;
                case Specification::TYPE_SELECT:
                    $productDetailsCache['specifications'][$specification->label] = $specification->pivot->specificationValue->value;
                    break;
                case Specification::TYPE_MULTI_SELECT:
                    $multiSelectValues = [];
                    foreach ($specification->pivot->specificationValues as $specificationValue) {
                        $multiSelectValues[] = $specificationValue->value;
                    }
                    $productDetailsCache['specifications'][$specification->label] = $multiSelectValues;
            }
        }

        dd($productDetailsCache);
        dd((new StoreBalanceService(351))->getBalance());
        Product::find(46)->update([
            'order' => null
        ]);
        dd('HERE');
        dump((new FlashMediaService(40))->getImageMedia());
        dd('HERE');
        $from = 146000;
        $to = 150000;
        $period = 100;
        DB::table('orders')->orderBy('id','desc')->limit(100)->chunk(100, function ($orders){
            foreach ($orders as $order){
                Order::updateTotalFields($order->id);
            }
        });
        dd('HERE');
        foreach (range($from, $to) as $order_id) {
            Order::updateTotalFields($order_id);
        }
        dd('HERE');
        while ($from <= $to) {
            testJob::dispatch($from, $from + $period)->delay(now()->addSeconds(10));
            $from += $period;
        }
        dd('HERE');
        dd(range(1,10));
        dd(class_basename($this));
        dd(Product::find(78)->varieties);
        (new StoreBalanceService($variety))->decrementFromStore(5, 'reduce');
        $variety = Variety::find(78);
        dd((new StoreBalanceService($variety))->getBalance());
        dd('HERE');

        $stores = Cache::get('stores');
        dd($stores[0]->balance);
        Cache::forget('stores');
        $stores = Cache::rememberForever('stores', function () {
            return Store::query()->select(['id','variety_id','balance'])->get();
        });
        dd($stores);

        dd(Store::all());
        dd((Product::class)->appends);
        $product = new Product();

        dd($product->appends);
        $product = Product::query()->with('varieties')->find(57);
        return response()->success('', compact('product'));


        $order = Order::find(146103);
        dd(!$order instanceof Order);
        Order::updateTotalFields(153200);
        dd(145866);
        $fakeRequestCarts = [
            ['variety_id' => 352, 'quantity' => 1, 'discount_price' => 0, 'price' => 255000],
            ['variety_id' => 513, 'quantity' => 1, 'discount_price' => 37400, 'price' => 149600],
            ['variety_id' => 525, 'quantity' => 3, 'discount_price' => 0, 'price' => 100000]
        ];

        $fakeCarts = [];
        foreach ($fakeRequestCarts as $requestFakeCart) {
            $newFakeCart = new Cart([
                'variety_id' => $requestFakeCart['variety_id'],
                'quantity' => $requestFakeCart['quantity'],
                'discount_price' => $requestFakeCart['discount_price'],
                'price' => $requestFakeCart['price'],
            ]);
            $newFakeCart->load(['variety' => function ($query) {$query->with('product');}]); /* todo: because of DontAppend method in final_price method in Variety, we are have to load product to have final_price attribute here. */
            $fakeCarts[] = $newFakeCart;
        }
        $carts = collect($fakeCarts);



//        return view('order_print_detail_pdf');
        $pdf = PDF::loadView('order_print_detail_pdf');
        return $pdf->stream('order_print_detail_pdf.pdf');

        $request = \Request();
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:orders,id'
        ]);
        $orders = Order::withCommonRelations()->with('childs')->with(['reservations' => function ($query) {
            $query->where('status', Order::STATUS_RESERVED);
        }])->parents()->whereIn('id', $request->ids)
            ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $request->ids) . ")"))
            ->get();

        foreach ($orders as $order) {
            $child_items = [];
            foreach ($order->childs as $child) {
                if (in_array($child->status, Order::ACTIVE_STATUSES)) {
                    foreach ($child->items->where('status', 1) as $ch_item) {
                        $child_items[] = $ch_item;

                    }
                }
            }
            foreach ($child_items as $child_order_item) {
                if (in_array($child_order_item->variety_id, $order->items->pluck('variety_id')->toArray())) {
                    foreach ($order->items as $item) {
                        if ($item->variety_id == $child_order_item->variety_id) {
                            if ($item->status == $child_order_item->status) {
                                $order->items->where('variety_id', $child_order_item->variety_id)->first()->quantity += $child_order_item->quantity;
                            } else {
                                $order->items->push($child_order_item);
                            }
                        }
                    }
                } else {
                    $order->items->push($child_order_item);
                }
            }
        }
//        return view('order_print_detail_pdf');

        $pdf = PDF::loadView('order_print_detail_pdf');
        return $pdf->download('order_print_detail_pdf'.'.pdf');
    }

    private function uniqueInviteCode()
    {
        do {
            $text = preg_replace('/[0-9]/', 'p', strtolower(Str::random(8)));
        } while (DB::table('customers')->where('invite_code', $text)->count() != 0);
        return $text;
    }




    public function add()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->boolean('transferd')->default(false);
        });
        dd('MIGRATION DONE');
        Schema::create('prices', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_main')->default(true);
            $table->foreignId('sell_type_id')->constrained('sell_types')->restrictOnDelete();

            $table->morphs('model');
            $table->integer('purchase_price')->nullable();
            $table->integer('unit_price');
            $table->integer('discount')->nullable();
            $table->enum('discount_type', ['percentage','flat'])->nullable();
            $table->timestamp('discount_until')->nullable();

            $table->timestamps();
        });
        dd('MIGRATION DONE');

        Schema::create('sell_types', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('label');
            $table->boolean('is_real');

            $table->timestamps();
        });
        dd('MIGRATION DONE');



//         Schema::table('orders', function (Blueprint $table) {
//             $table->timestamp('reserved_at')->nullable();
//             $table->unsignedBigInteger('discount_on_order')->nullable();
//             $table->unsignedBigInteger('discount_on_coupon')->nullable();
//             $table->unsignedBigInteger('discount_on_items')->nullable();
//             $table->unsignedBigInteger('pay_by_wallet_gift_balance')->nullable();
//             $table->unsignedBigInteger('pay_by_wallet_main_balance')->nullable();
//             $table->unsignedBigInteger('total_items_amount')->nullable();
//         });
//         dd('MIGRATION DONE');
        // Schema::table('posts', function (Blueprint $table) {
        //     $table->integer('read_time')->nullable();
        // });
        // dd('MIGRATION DONE');
        // Schema::table('admins', function (Blueprint $table) {
        //     $table->rememberToken();
        // });
        // dd('MIGRATION DONE');

        // Schema::table('order_updaters', function (Blueprint $table) {
        //     $table->timestamp('expires_at')->nullable();
        // });
        // dd('MIGRATION DONE');


        // Schema::create('order_updaters', function (Blueprint $table) {
        //     $table->id();

        //     $table->unsignedBigInteger('payable_amount');
        //     $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
        //     $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
        //     $table->string('update_type');
        //     $table->json('update_items');
        //     $table->timestamp('expires_at');
        //     $table->boolean('is_done')->default(false);
        //     $table->string('unique_code')->unique();

        //     $table->timestamps();
        // });

        // Schema::table('invoices', function (Blueprint $table) {

        //     $table->boolean('has_reduced_gift_wallet')->default(false);

        // });


        // dd('MIGRATION DONE');
        // Schema::table('orders', function (Blueprint $table) {

        //     $table->unsignedBigInteger('total_discount_on_orders')->nullable();

        // });


        // Schema::table('orders', function (Blueprint $table) {

        //     $table->unsignedInteger('children_count')->nullable();
        //     $table->unsignedInteger('total_quantity')->nullable();
        //     $table->unsignedInteger('total_items_count')->nullable();
        //     $table->unsignedBigInteger('discount_on_products')->nullable();
        //     $table->unsignedBigInteger('total_products_prices_with_discount')->nullable();
        //     $table->unsignedBigInteger('total_shipping_amount')->nullable();
        //     $table->unsignedBigInteger('paid_by_wallet_gift_balance')->nullable();
        //     $table->unsignedBigInteger('paid_by_wallet_main_balance')->nullable();
        //     $table->unsignedBigInteger('total_invoices_amount')->nullable();

        // });
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_magazine')->default(false);
        });
        dd('MIGRATION DONE');
         Schema::table('recommendations', function (Blueprint $table) {
             $table->string('link')->nullable();
             $table->unsignedBigInteger('linkable_id')->nullable();
             $table->string('linkable_type')->nullable();
         });
        dd('MIGRATION DONE');
//         Schema::table('recommendations', function (Blueprint $table) {
//                 $table->dropColumn('group');
//                 $table->dropColumn('order');
//                 $table->dropColumn('product_id');
//             $table->string('group_name')->unique();
//             $table->string('title')->nullable();
//             $table->boolean('status')->default(1);
//
//         });
         Schema::create('recommendation_items', function (Blueprint $table) {
             $table->id();
             $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
             $table->foreignId('recommendation_id')->constrained('recommendations')->cascadeOnDelete();
             $table->integer('priority')->default(1);
             $table->timestamps();
         });
         dd('MIGRATION DONE');
    }

//    public function table()
//    {
//       $provinces = Province::query()->get();
//        $titles = ['id'=>'شناسه','name'=>'نام استان','ops'=>'عملیات'];
//        return view('admin.table-test',compact('provinces','titles'));
//    }

public function transfer()
{



    for ($i = 0 ; $i<count($this->array);$i++){

        yield $this->array[$i];

    }

}

public function main()
{



    if (!Schema::connection('extra')->hasTable('test')){

    Schema::connection('extra')->create('test', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->string('status')->nullable();
        $table->string('creatorable_type');
        $table->unsignedBigInteger('creatorable_id');
        $table->string('updaterable_type')->nullable();
        $table->unsignedBigInteger('updaterable_id')->nullable();
        $table->timestamps();
    });
    }

//    OrderStatusLog::query()->where('transferd',0)->chunk(1000,function ($log) {
//        $this->array = $log->toArray();
//        foreach ($this->transfer() as $data){
//
//            DB::connection('extra')->table('test')->insert($data);
//            DB::table('order_status_log')->where('id',$data['id'])->update(['transferd'=>1]);
//        }
//        $this->array = [];
//    });
//
//    return response('data sent');


          OrderStatusLog::query()->chunk(1000,function ($logs){

//        $this->array = $logs->toArray();
//        foreach ($this->transfer() as $data){
//
//            DB::connection('extra')->table('test')->insert($data);
//
//        }
//        $this->array = [];
        foreach ($logs as $log) {
            $data = [
                'order_id' => $log->order_id,
                'status' => $log->status,
                'creatorable_type' => $log->creatorable_type,
                'creatorable_id' => $log->creatorable_id,
                'updaterable_type' => $log->updaterable_type,
                'updaterable_id' => $log->updaterable_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            DB::connection('extra')->table('test')->insert($data);

                DB::table('order_status_logs')->where('id',$log->id)->update(['transferd'=>true]);
        }
    });

}

}

//}

