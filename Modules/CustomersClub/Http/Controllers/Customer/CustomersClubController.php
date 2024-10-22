<?php

namespace Modules\CustomersClub\Http\Controllers\Customer;

use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\CustomersClub\Entities\CustomersClubBonConvertRequest;
use Modules\CustomersClub\Entities\CustomersClubBeforeAfter;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\CustomersClub\Entities\CustomersClubSellScore;
use Modules\CustomersClub\Entities\CustomersClubSetting;
use Modules\CustomersClub\Http\Requests\BonConvertRequest as BCRRequest;
use Modules\CustomersClub\Http\Requests\SetBeforeAfterImageCustomer;

class CustomersClubController extends Controller
{
    public function getBoughtProducts()
    {
        $customer_id = Auth::user()->id;
        $orders = DB::table('orders')
            ->whereIn('status',['new','delivered','in_progress'])
            ->where('customer_id',$customer_id)
            ->pluck('id');

        $order_items = DB::table('order_items')
            ->whereIn('order_id',$orders)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        $sent_product_ids = DB::table('customers_club_before_afters')
            ->where('customer_id',$customer_id)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        $remain_product_ids = array_diff($order_items, $sent_product_ids);

        $products = DB::table('products')
            ->whereIn('id',$remain_product_ids)
            ->select('id','title')
            ->get();

        foreach ($products as $product) {
            $product->images = Helpers::getImages('Product',$product->id);
            if (!$product->images){
                $varieties = DB::table('varieties')->select('id')->where('product_id',$product->id)->get();
                foreach ($varieties as $variety) {
                    if ($v_images = Helpers::getImages('Variety',$variety->id)){
                        $product->images[] = $v_images[0];
                    }
                }
            }
        }

//        dd($products);
//        dd($order_items,$sent_product_ids,$remain_product_ids);
        return response()->success('محصولات کاربر با موفقیت دریافت شد',['products'=>$products]);
    }

    public function setBeforeAfterImage(SetBeforeAfterImageCustomer $request)
    {
        $customer_id = Auth::user()->id;
        $exist = CustomersClubBeforeAfter::where('customer_id',$customer_id)->where('product_id',$request->product_id)->get();
        if(count($exist->toArray()) ==0){
            // تا حالا ثبت نشده است
            //media
            if ($request->hasFile('before_image') && $request->hasFile('after_image')) {
                $imageBefore = new CustomersClubBeforeAfter($request->all());
                $imageBefore->customer_id = $customer_id;
                $imageBefore->type = 'before';
                $imageBefore->approved = 0;
                $imageBefore->description = $request->description;
                $imageBefore->save();
                $imageBefore->saveFileSpatieMedia($request->before_image,'customers_club_before');

                $imageAfter = new CustomersClubBeforeAfter($request->all());
                $imageAfter->customer_id = $customer_id;
                $imageAfter->type = 'after';
                $imageAfter->description = null;
                $imageAfter->approved = 0;
                $imageAfter->save();
                $imageAfter->saveFileSpatieMedia($request->after_image,'customers_club_after');

                // (new \Modules\Core\Helpers\Helpers)->setScoreForBeforeAfterImages($customer_id, $request->product_id);
            }
            return response()->success('تصاویر قبل و بعد محصول با موفقیت ثبت شد');
        } else {
            (new \Modules\Core\Helpers\Helpers)->setScoreForBeforeAfterImages($customer_id, $request->product_id);
            return response()->error('تصاویر قبل و بعد محصول قبلاً ثبت شده است');
        }
    }

    public function getClubScoreList()
    {
        $customer_id = Auth::user()->id;
        $scores = CustomersClubScore::query()
            ->where('customer_id',$customer_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
        return response()->success('لیست امتیازات کسب شده توسط کاربر', compact('scores'));
    }
    public function getClubData()
    {
        $customer_id = Auth::user()->id;
        $customer = Customer::find($customer_id);
        $lastBonValue = CustomersClubSetting::where('key','benedi_bon')->latest('id')->value('value');
        $data = [
            'customers_club_score' => $customer->customers_club_score,
            'customers_club_bon' => $customer->customers_club_bon,
            'customers_club_level' => $customer->customers_club_level,
            'bon_value' => $lastBonValue,
        ];
        return response()->success('داده های باشگاه مشتریان مربوط به کاربر', compact('data'));
    }

    public function sendBonConvertRequest(BCRRequest $request)
    {
        $customer_id = Auth::user()->id;
        $customer = Customer::find($customer_id);

        $exist = CustomersClubBonConvertRequest::where('customer_id',$customer_id)->where('status','new')->first();
        if ($exist){
            return response()->error('شما یک درخواست بررسی نشده دارید');
        } elseif ($customer->customers_club_bon < $request->bon) {
            return response()->error('مقدار بن موجودی شما از میزان درخواست شده کمتر است');
        } else {
            $bcr = new CustomersClubBonConvertRequest();
            $bcr->customer_id = $customer_id;
            $bcr->requested_bon = $request->bon;
            $bcr->status = 'new';
            $bcr->request_date = date('Y-m-d');
            $bcr->save();
            return response()->success('درخواست شما با موفقیت ثبت شد',compact('bcr'));
        }
    }

    public function getBonConvertRequestList()
    {
        $customer_id = Auth::user()->id;

        $list = CustomersClubBonConvertRequest::query()
            ->select(
                "requested_bon",
                "converted_gift_value",
                "status",
                "request_date",
                "action_date",
                "description",
            )
            ->where('customer_id',$customer_id)
            ->get();
        return response()->success('لیست درخواست های تبدیل بن به هدیه کیف پول کاربر',compact('list'));
    }

    public function getMissions()
    {
        $customer_id = Auth::user()->id;

        $sell_scores = CustomersClubSellScore::orderBy('min_value')->get();
        $sell_scores_table = [
            [
                'مبلغ خرید',
                'امتیاز',
                'بن'
            ]
        ];
        foreach ($sell_scores as $sell_score) {
            $sell_scores_table[] = [
                $sell_score->title,
                $sell_score->score_value,
                $sell_score->bon_value,
            ];
        }

        $missions = [
            [
                'cause_id' => '1',
                'cause_title' => 'ثبت نام در وب سایت و تکمیل پروفایل',
                'cause_description' => [
                    'امتیاز این قسمت به صورت یک بار و پس از اولین تکمیل پروفایل به مشتری تعلق می گیرد.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/01.jpg',
                'result' => '',
                'type' => 'single',
                'postfix' => '',
                'link_to' => 'profile',
            ],
            [
                'cause_id' => null,
                'cause_title' => 'خرید از وب سایت',
                'cause_description' => [
                    'امتیاز هر خرید با توجه به مبلغ خرید و از جدول روبرو تعیین می گردد.',
                ],
                'extra_data' => $sell_scores_table,
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/02.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'خرید',
                'link_to' => 'categories',
            ],
            [
                'cause_id' => '2',
                'cause_title' => 'نوشتن نظر برای محصول خریداری شده',
                'cause_description' => [
                    'امتیاز این قسمت به ازای هر محصول فقط یک بار به مشتری داده می شود.',
                    'البته تعلق گرفتن امتیاز به مشتری فقط در صورتی که ادمین نظر را تأیید کند امکان پذیر خواهد بود.'
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/03.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'نظر',
                'link_to' => 'comments',
            ],
            [
                'cause_id' => '3',
                'cause_title' => 'عکس قبل و بعد از مصرف محصولات',
                'cause_description' => [
                    'امتیاز این قسمت به ازای هر محصول فقط یک بار به مشتری داده می شود.',
                    'البته تعلق گرفتن امتیاز به مشتری فقط در صورتی که ادمین تصاویر ارسال شده توسط مشتری را تأیید کند امکان پذیر خواهد بود.',
                    'نکته: در صورتی که برای یک محصول تصویری توسط مشتری ارسال شود، تا وقتی که مدیر آن را تأیید یا رد نکند، مشتری نمی تواند برای محصول موردنظر تصویر دیگری را ارسال نماید.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/04.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'تصویر',
                'link_to' => 'before-after',
            ],
            [
                'cause_id' => '4',
                'cause_title' => 'استفاده از لینک معرف برای ثبت نام',
                'cause_description' => [
                    'در صورتی که مشتری جدیدی هنگام ثبت نام شماره موبایل معرف را ثبت نماید، امتیاز این بخش به معرف تعلق می گیرد.',
                    'برای دریافت امتیاز این قسمت محدودیتی وجود ندارد.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/05.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'نفر',
                'link_to' => null,
            ],
            [
                'cause_id' => '5',
                'cause_title' => 'اولین خرید توسط کسی که با لینک معرف ثبت نام کرده است',
                'cause_description' => [
                    'در صورتی که مشتری جدیدی که شماره کسی را به عنوان معرف ثبت کرده است اولین خرید خود را نهایی نماید، امتیاز این بخش به معرف وی تعلق خواهد گرفت.',
                    'این امتیاز فقط برای اولین خرید مشتری جدید برای معرف لحاظ می گردد با این شرط که مبلغ خرید حتماً از کف مبلغ تعیین شده توسط ادمین بیشتر باشد.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/06.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'نفر',
                'link_to' => null,
            ],
            [
                'cause_id' => '6',
                'cause_title' => 'منشن کردن پیج ما در استوری به مدت 24 ساعت',
                'cause_description' => [
                    'با ارسال اسکرین شات یا اطلاع رسانی از روش های دیگر توسط مشتری که پیج فروشگاه را تگ کرده باشد امتیاز این بخش به مشتری تعلق می گیرد.',
                    'برای امتیاز این بخش محدودیتی از لحاظ تعداد درنظر گرفته نشده است. ولی برای ارسال و امتیاز گیری مجدد، حتماً باید 24 ساعت از ثبت آخرین امتیاز این بخش برای مشتری گذشته باشد.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/07.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'بار',
                'link_to' => 'instagram',
            ],
            [
                'cause_id' => '7',
                'cause_title' => 'شرکت در نظر سنجی اینماد',
                'cause_description' => [
                    'با شرکت در نظرسنجی سایت اینماد و ارسال اسکرین شات یا اطلاع رسانی از روش های دیگر توسط مشتری امتیاز این بخش به مشتری تعلق می گیرد.',
                    'این امتیاز فقط یک بار برای مشتری قابل دریافت خواهد بود.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/08.jpg',
                'result' => '',
                'type' => 'single',
                'postfix' => '',
                'link_to' => 'enamad',
            ],
            [
                'cause_id' => '8',
                'cause_title' => 'شرکت در تکمیل پرسشنامه ها و مسابقات',
                'cause_description' => [
                    'این بخش پس از برنامه ریزی و تکمیل به باشگاه مشتریان افزوده خواهد شد.',
                    'این امتیاز فقط یک بار برای مشتری قابل دریافت خواهد بود.'
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/09.jpg',
                'result' => '',
                'type' => 'single',
                'postfix' => '',
                'link_to' => null,
            ],
            [
                'cause_id' => '9',
                'cause_title' => 'لاگین شدن روزانه در pwa',
                'cause_description' => [
                    'با هر بار ورود کاربر به نرم افزار PWA سایت، مشتری امتیاز این بخش را دریافت خواهد کرد.',
                    'این امتیاز فقط یک بار در روز برای مشتری قابل دریافت خواهد بود.',
                ],
                'extra_data' => [],
                'total_score' => 0,
                'total_bon' => 0,
                'image' => 'storage/missions/10.jpg',
                'result' => '',
                'type' => 'multiple',
                'postfix' => 'بار',
                'link_to' => null,
            ],

        ];

        foreach ($missions as $index => $mission) {
            $user_data = CustomersClubScore::query()
                ->where('customer_id',$customer_id)
                ->where('cause_id',$mission['cause_id'])
                ->select(
                    DB::raw('sum(score_value) as scores'),
                    DB::raw('sum(bon_value) as bons'),
                    DB::raw('count(*) as quantity'),
                )
                ->first();
            $missions[$index]['total_score'] = $user_data->scores;
            $missions[$index]['total_bon'] = $user_data->bons;
            if ($mission['type']=='single'){
                $missions[$index]['result'] = $user_data->quantity > 0;
            } else {
                $missions[$index]['result'] = $user_data->quantity . ' ' . $mission['postfix'];
            }

            unset($missions[$index]['postfix']);
//            unset($missions[$index]['type']);
            unset($missions[$index]['cause_id']);
        }

        return response()->success('مأموریت های باشگاه مشتریان',compact('missions'));
    }


    public function getTopTen()
    {
        $current_date = Verta::now();


        $start_month = Helpers::toGregorian(strval($current_date->year).'/'.strval($current_date->month).'/01');
        $end_month =now();

        $last_month = CustomersClubScore::whereBetween('created_at', [$start_month, $end_month])
            ->select(
                'customer_id',
                'created_at',
                DB::raw('sum(score_value) as score'),
            )
            ->groupBy('customer_id')
            ->orderBy('score','desc')
            ->take(10)
            ->get();

        foreach ($last_month as $item) {
            $item->customers_club_level = Customer::find($item->customer_id)->customers_club_level;
            $item->makeHidden('cause');
            $item->makeHidden('product');
            $item->makeHidden('mobile');
            $item->makeHidden('extra_customer');
            $item->makeHidden('customer_id');
        }


        $start_year = Helpers::toGregorian(strval($current_date->year).'/01/01');
        $end_year =Helpers::toGregorian(strval($current_date->year).'/12/29');
        $last_year = CustomersClubScore::whereBetween('created_at', [$start_year, $end_year])
            ->select(
                'customer_id',
                'created_at',
                DB::raw('sum(score_value) as score'),
            )
            ->groupBy('customer_id')
            ->orderBy('score','desc')
            ->take(10)
            ->get();

        foreach ($last_year as $item) {
            $item->customers_club_level = Customer::find($item->customer_id)->customers_club_level;
            $item->makeHidden('cause');
            $item->makeHidden('product');
            $item->makeHidden('mobile');
            $item->makeHidden('extra_customer');
            $item->makeHidden('customer_id');
        }

        $all_time = CustomersClubScore::query()
            ->select(
                'customer_id',
                DB::raw('sum(score_value) as score'),
            )
            ->groupBy('customer_id')
            ->orderBy('score','desc')
            ->take(10)
            ->get();

        foreach ($all_time as $item) {
            $item->customers_club_level = Customer::find($item->customer_id)->customers_club_level;
            $item->makeHidden('cause');
            $item->makeHidden('product');
            $item->makeHidden('mobile');
            $item->makeHidden('extra_customer');
            $item->makeHidden('customer_id');
        }

        $top_ten = [
            'last_month' => $last_month,
            'last_year' => $last_year,
            'all_time' => $all_time,
        ];

        return response()->success('لیست 10 نفر برتر امتیازات دوره ای', compact('top_ten'));
    }
}
