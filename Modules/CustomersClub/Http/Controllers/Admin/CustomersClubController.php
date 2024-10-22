<?php

namespace Modules\CustomersClub\Http\Controllers\Admin;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\CustomersClub\Entities\CustomersClubBeforeAfter;
use Modules\CustomersClub\Entities\CustomersClubBonConvertRequest;
use Modules\CustomersClub\Entities\CustomersClubGetScore;
use Modules\CustomersClub\Entities\CustomersClubLevel;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\CustomersClub\Entities\CustomersClubSellScore;
use Modules\CustomersClub\Entities\CustomersClubSetting;
use Modules\CustomersClub\Exports\UserLevelExport;
use Modules\CustomersClub\Http\Requests\BonConvertRequestUpdate;
use Modules\CustomersClub\Http\Requests\SetBeforeAfterImage;
use Modules\Product\Entities\Product;
use Modules\Product\Services\ProductsCollectionService;
use MongoDB\Driver\Session;

class CustomersClubController extends Controller
{
    public function setBeforeAfterImage(SetBeforeAfterImage $request)
    {
        $exist = CustomersClubBeforeAfter::where('customer_id',$request->customer_id)->where('product_id',$request->product_id)->get();
        if(count($exist->toArray()) ==0){
            // تا حالا ثبت نشده است
            //media
            if ($request->hasFile('before_image') && $request->hasFile('after_image')) {
                $imageBefore = new CustomersClubBeforeAfter($request->all());
                $imageBefore->type = 'before';
                $imageBefore->approved = 1;
                $imageBefore->save();
                $imageBefore->saveFileSpatieMedia($request->before_image,'customers_club_before');

                $imageAfter = new CustomersClubBeforeAfter($request->all());
                $imageAfter->type = 'after';
                $imageAfter->approved = 1;
                $imageAfter->save();
                $imageAfter->saveFileSpatieMedia($request->after_image,'customers_club_after');

                (new \Modules\Core\Helpers\Helpers)->setScoreForBeforeAfterImages($request->customer_id, $request->product_id);
            }
            ActivityLogHelper::storeModel('تصاویر قبل و بعد باشگاه ثبت شد', $imageAfter);
            if (request()->header('Accept') == 'application/json') {
                return response()->success('تصاویر قبل و بعد محصول با موفقیت ثبت شد');
            }
            return redirect()->route('admin.customersClub.getBeforeAfterImages')
            ->with('success', 'تصاویر قبل و بعد با موفقیت ثبت شد.');
        } else {
            (new \Modules\Core\Helpers\Helpers)->setScoreForBeforeAfterImages($request->customer_id, $request->product_id);
            return response()->error('تصاویر قبل و بعد محصول قبلاً ثبت شده است');
        }
    }

    public function getBeforeAfterImage(Request $request)
    {
        if (request()->header('Accept') == 'application/json') {

            $approved = (isset($request->approved)&&$request->approved==1)?1:0;
            $exist = CustomersClubBeforeAfter::where('approved',$approved)->where('type','before')->get();

            $list = array();
            foreach ($exist as $item) {
                $before_id = $item->id;
                $after_id = CustomersClubBeforeAfter::where('customer_id',$item->customer_id)->where('product_id',$item->product_id)->where('type','after')->first()->id;
                $list[] = [
                    'id' => $item->id,
                    'customer' => [
                        'id' => $item->customer_id,
                        'full_name' => $item->customer->first_name
                    ],
                    'product' => [
                        'id' => $item->product_id,
                        'title' => $item->product->title,
                        'images' => Helpers::getImages('Product',$item->product_id)
                    ],
                    'before_image' => Helpers::getImages('CustomersClubBeforeAfter',$before_id),
                    'after_image' => Helpers::getImages('CustomersClubBeforeAfter',$after_id),
                    'description' => CustomersClubBeforeAfter::where('customer_id',$item->customer_id)->where('product_id',$item->product_id)->where('type','before')->first()->description
                ];
            }
            return response()->success('تصاویر ثبت شده قبل و بعد محصول: ' . ($approved?'تأیید شده':'تأیید نشده'), $list);
        }
        $exist = CustomersClubBeforeAfter::where('type', 'before')->get();

        $afterBeforImages = [];
        foreach ($exist as $item) {
            $before_id = $item->id;

            // بررسی وجود رکورد برای نوع "بعد"
            $afterRecord = CustomersClubBeforeAfter::where('customer_id', $item->customer_id)
                ->where('product_id', $item->product_id)
                ->where('type', 'after')
                ->first();

            $after_id = $afterRecord ? $afterRecord->id : null; // اگر رکورد وجود نداشت، null تنظیم می‌شود

            $afterBeforImages[] = [
                'id' => $item->id,
                'approved' => $item->approved,
                'customer' => [
                    'id' => $item->customer_id,
                    'full_name' => $item->customer->first_name,
                ],
                'product' => [
                    'id' => $item->product_id,
                    'title' => $item->product->title,
                    'images' => Helpers::getImages('Product', $item->product_id),
                ],
                'before_image' => Helpers::getImages('CustomersClubBeforeAfter', $before_id),
                'after_image' => $after_id ? Helpers::getImages('CustomersClubBeforeAfter', $after_id) : null, // استفاده از null اگر رکورد وجود نداشت
                'description' => $item->description, // استفاده از description موجود در رکورد "قبل"
            ];
        }
        $products = (new ProductsCollectionService())->getProductsCollection();
        
        return view('customersclub::admin.afterBeforImage.index',compact('afterBeforImages','products'));
    }

    public function approveBeforeAfterImage(Request $request)
    {
        $before = CustomersClubBeforeAfter::find($request->before_after_id);
        if($before->approved == 0){
            $after = CustomersClubBeforeAfter::where('customer_id',$before->customer_id)->where('product_id',$before->product_id)->where('type','after')->first();

            $before->approved = 1;
            $before->save();

            $after->approved = 1;
            $after->save();

            (new \Modules\Core\Helpers\Helpers)->setScoreForBeforeAfterImages($before->customer_id, $before->product_id);
            ActivityLogHelper::updatedModel('تصاویر قبل و بعد تایید شد', $after);
            if (request()->header('Accept') == 'application/json') {
                return response()->success('تصاویر قبل و بعد محصول با موفقیت تأیید شد');
            }else{
                return redirect()->route('admin.customersClub.getBeforeAfterImages')
                ->with('success', 'تصاویر قبل و بعد محصول با موفقیت تأیید شد');
            }
        } else {
            if (request()->header('Accept') == 'application/json') {
                return response()->error('تصاویر قبل و بعد محصول قبلاً تأیید شده است');
            }
            return redirect()->route('admin.customersClub.getBeforeAfterImages')
            ->with('success', 'تصاویر قبل و بعد محصول قبلا تأیید شده.');
        }
    }

    public function deleteBeforeAfterImage(Request $request)
    {
        $rules = [
            'before_after_id' => 'required'
        ];
        $request->validate($rules);

        $beforeAfter = CustomersClubBeforeAfter::find($request->before_after_id);
        if (!$beforeAfter){
            return response()->error('مورد درخواست شده یافت نشد');
        } elseif ($beforeAfter->approved){
            return response()->error('تصاویر تأیید شده قابل حذف نیستند');
        }


        $after_id = CustomersClubBeforeAfter::where('customer_id',$beforeAfter->customer_id)->where('product_id',$beforeAfter->product_id)->where('type','after')->first()->id;
        DB::table('media')->where('model_type','Modules\CustomersClub\Entities\CustomersClubBeforeAfter')->whereIn('model_id',[$beforeAfter->id,$after_id])->delete();
        $beforeAfterImage = DB::table('customers_club_before_afters')->whereIn('id',[$beforeAfter->id,$after_id])->delete();
        ActivityLogHelper::deletedModel('تصاویر ثبت شده قبل و بعد محصول با موفقیت حذف شد', $beforeAfterImage);

        return response()->success('تصاویر ثبت شده قبل و بعد محصول با موفقیت حذف شد');
    }

    public function getBoughtProducts()
    {
        $orders = DB::table('orders')
            ->whereIn('status',['new','delivered','in_progress'])
            ->where('customer_id',request()->customer_id)
            ->pluck('id');

        $order_items = DB::table('order_items')
            ->whereIn('order_id',$orders)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        $sent_product_ids = DB::table('customers_club_before_afters')
            ->where('customer_id',request()->customer_id)
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

    public function setStoryMention(Request $request)
    {
        $rules = [
            'customer_id' => 'required'
        ];
        $request->validate($rules);

        $get_score = Helpers::getCustomersClubScoreByKey('mentioned_instagram_on_story');  // دریافت امتیازی که بابت منشن کردن در استوری به مشتری تعلق می گیرد

        if($get_score){
            // در صورتی که امتیازی برای این مرحله درنظر گرفته شده باشد
            $exist = CustomersClubScore::where('customer_id',$request->customer_id)->where('cause_id',$get_score->id)->latest()->first();
            if(!$exist){
                // تا حالا ثبت نشده است

                (new \Modules\Core\Helpers\Helpers)->setScoreForStoryMention($request->customer_id,$get_score);
                if (request()->header('Accept') == 'application/json') {
                    return response()->success('امتیاز منشن کردن در استوری با موفقیت ثبت شد');
                }
                return redirect()->route('admin.customersClub.pageMinStoryHours')
                ->with('success', 'امتیاز منشن کردن در استوری با موفقیت ثبت شد');
            } else {
                $now = Carbon::parse(now()->format('Y-m-d H:i:s'));
                $get_setting = Helpers::getCustomersClubSettingByKey('min_story_hours');  // دریافت مقدار حداقل ساعت برای ثبت استوری (منشن کردن)
                $customer_club_score = CustomersClubScore::query()
                    ->where('customer_id',$request->customer_id)
                    ->where('cause_id', $get_score->id)
                    ->first();
                $old = Carbon::parse($customer_club_score->created_at);
                $secondsDifference = $now->diffInSeconds($old);

                $time_for_allow_mention = $get_setting->value * 3600;

                if($secondsDifference > $time_for_allow_mention){
                    (new \Modules\Core\Helpers\Helpers)->setScoreForStoryMention($request->customer_id,$get_score);
                    if (request()->header('Accept') == 'application/json') {
                        return response()->success('امتیاز منشن کردن در استوری با موفقیت ثبت شد');
                    }
                    return redirect()->route('admin.customersClub.pageMinStoryHours')
                    ->with('success', 'امتیاز منشن کردن در استوری با موفقیت ثبت شد');
                } else {
                    if (request()->header('Accept') == 'application/json') {
                        return response()->error("برای ثبت مجدد امتیاز منشن کردن در استوری باید حداقل $get_setting->value ساعت از ثبت مورد قبلی گذشته باشد.");
                    }
                    return redirect()->route('admin.customersClub.pageMinStoryHours')
                    ->with('error', "برای ثبت مجدد امتیاز منشن کردن در استوری باید حداقل $get_setting->value ساعت از ثبت مورد قبلی گذشته باشد.");
                }
            }
        } else {
            if (request()->header('Accept') == 'application/json') {
                return response()->error('برای این عملیات امتیازی درنظر گرفته نشده است.');
            }
            return redirect()->route('admin.customersClub.pageMinStoryHours')
            ->with('error', 'برای این عملیات امتیازی درنظر گرفته نشده است.');
        }
    }
    public function pageEnamadScore()
    {
        return view('customersclub::admin.enamad.index');
    }
    public function setEnamadScore(Request $request)
    {
        $rules = [
            'customer_id' => 'required'
        ];
        $request->validate($rules);

        $get_score = Helpers::getCustomersClubScoreByKey('participate_in_enamad_survey');  // دریافت امتیازی که بابت ثبت نظر در اینماد به مشتری تعلق می گیرد

        if($get_score){
            // در صورتی که امتیازی برای این مرحله درنظر گرفته شده باشد
            $exist = CustomersClubScore::where('customer_id',$request->customer_id)->where('cause_id',$get_score->id)->first();
            if(!$exist){
                // تا حالا ثبت نشده است
                (new \Modules\Core\Helpers\Helpers)->setScoreForEnamadSurvey($request->customer_id,$get_score);
                if (request()->header('Accept') == 'application/json') {
                    return response()->success('امتیاز شرکت در نظرسنجی اینماد با موفقیت ثبت شد');
                }
                return redirect()->route('admin.customersClub.pageMinStoryHours')
                ->with('success','امتیاز شرکت در نظرسنجی اینماد با موفقیت ثبت شد');
            } else {
                if (request()->header('Accept') == 'application/json') {
                    return response()->error("امتیاز این بخش قبلاً ثبت شده است");
                }
                return redirect()->route('admin.customersClub.pageMinStoryHours')
                ->with('error', "امتیاز این بخش قبلاً ثبت شده است");
            }
        } else {
            if (request()->header('Accept') == 'application/json') {
                return response()->error('برای این عملیات امتیازی درنظر گرفته نشده است.');
            }
            return redirect()->route('admin.customersClub.pageMinStoryHours')
            ->with('error', 'برای این عملیات امتیازی درنظر گرفته نشده است.');
        }
    }

    public function setMinFirstOrder(Request $request)
    {
        $rules = [
            'min_value' => 'required'
        ];
        $request->validate($rules);

        $get_setting = Helpers::getCustomersClubSettingByKey('min_first_order');  // دریافت مقدار حداقل میزان اولین خرید برای ثبت امتیاز برای معرف

        $setting = CustomersClubSetting::find($get_setting->id);
        $setting->update([
            'value' => $request->min_value
        ]);

        return response()->success('با موفقیت به روز شد',['min_first_order'=>$setting->value]);
    }

    public function pageMinStoryHours()
    {
        return view('customersclub::admin.instagramStory.index');
    }
    public function setMinStoryHours(Request $request)
    {
        $rules = [
            'min_value' => 'required'
        ];
        $request->validate($rules);

        $get_setting = Helpers::getCustomersClubSettingByKey('min_story_hours');  // دریافت مقدار حداقل ساعت برای ثبت استوری (منشن کردن)

        $setting = CustomersClubSetting::find($get_setting->id);
        $setting->update([
            'value' => $request->min_value
        ]);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('با موفقیت به روز شد',['min_story_hours'=>$setting->value]);
        }
        return redirect()->route('admin.customersClub.pageMinStoryHours')
        ->with('success', 'تعیین بازه با موفقیت ثبت شد');
    }

    public function setDailyLoginScore()
    {
        $customer = \Auth::guard('customer-api')->user();
//        Log::info($customer ?"Logged in : " . $customer->id : "Not Logged in");

        $get_score = Helpers::getCustomersClubScoreByKey('daily_login_in_pwa');  // دریافت امتیازی که بابت لاگین روزانه در نرم‌افزار به مشتری تعلق می گیرد

        if ($customer){
            if($get_score){
                // در صورتی که امتیازی برای این مرحله درنظر گرفته شده باشد
                $exist = CustomersClubScore::where('customer_id',$customer->id)->where('cause_id',$get_score->id)->latest()->first();
                if(!$exist){
                    // تا حالا ثبت نشده است
                    (new \Modules\Core\Helpers\Helpers)->setScoreForDailyLogin($customer->id,$get_score);
//                return response()->success('امتیاز لاگین روزانه در نرم‌افزار با موفقیت ثبت شد');
                    Log::info('امتیاز لاگین روزانه در نرم افزار با موفقیت ثبت شد');
                } else {
                    $now = date('Y-m-d');
                    $customer_club_score = CustomersClubScore::query()
                        ->where('customer_id',$customer->id)
                        ->where('cause_id', $get_score->id)
                        ->orderBy('date','desc')
                        ->first();

//                Log::info('امتیاز از قبل موجود است ' . $customer_club_score->id);

                    if($now > $customer_club_score->date){
                        (new \Modules\Core\Helpers\Helpers)->setScoreForStoryMention($customer->id,$get_score);
                        Log::info('امتیاز لاگین روزانه در نرم افزار با موفقیت ثبت شد');
                    } else {
//                    Log::info('امتیاز ورود کاربر به نرم افزار برای تاریخ جاری ثبت شده است');
                    }
                }
            } else {
//            return response()->error('برای این عملیات امتیازی درنظر گرفته نشده است.');
                Log::info('برای ورود روزانه کاربر در نرم افزار امتیازی درنظر گرفته نشده است');
            }
        }
    }

    public function getClubScoreList()
    {
        $scores = CustomersClubScore::query()
            ->where('customer_id',request()->customer_id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست امتیازات کسب شده توسط کاربر', compact('scores'));
        }
        return view('customersclub::admin.reports.club-store-list.index', compact('scores'));
    }

    public function getLevelUsers()
    {
        $request = \Request();
        $usersQuery = Customer::query()
            ->leftJoin('customers_club_scores', 'customers.id', '=', 'customers_club_scores.customer_id')
            ->when($request->from_date && $request->to_date, function(Builder $q) use ($request) {
                $q->where('customers_club_scores.created_at', '>=', $request->from_date)
                    ->where('customers_club_scores.created_at', '<=', $request->to_date);
            })
            ->when($request->customer_id,function(Builder $q) use ($request){
                $q->where('customers.id', $request->customer_id);
            })
            ->select('customers.*', DB::raw('SUM(customers_club_scores.score_value) as total_score'))
            ->groupBy('customers.id')
            ->orderBy('total_score', 'desc');

        if (\request()->header('accept') == 'x-xlsx') {
//            $users = Customer::query()->get();
            $users = $usersQuery->get();

            $final_list = [];

            foreach ($users as $user){
                $final_list [] = [
                    $user->mobile,
                    $user->first_name . " " . $user->last_name,
                    $user->customers_club_level['level'],
                    $user->customers_club_score,
                    $user->customers_club_bon,
                ];
            }

            return Excel::download(new UserLevelExport($final_list),
                __FUNCTION__ . '-' . now()->toDateString() . '.xlsx');
        }

        #slow
//        $users = Customer::query()->get()->sortByDesc('customers_club_score');
//        $users = $users->filter(function ($user) {
//            return $user->customers_club_score != 0;
//        });

        $users = $usersQuery->paginate(15);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست کاربران به همراه امتیاز و سطح', compact('users'));
        }

        return view('customersclub::admin.reports.points-and-level.index', compact('users'));
//        $users = Customer::query()
//            ->sortByDesc('customers_club_score')
//            ->paginate(20);
    }

    public function getClubScores()
    {
        $list = CustomersClubGetScore::select('id','title','score_value','bon_value')->orderBy('id','desc')->get();
        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست امتیازات باشگاه مشتریان', compact('list'));
        }
        return view('customersclub::admin.setting.score', compact('list'));
    }

    public function setClubScores(Request $request)
    {
        $data = $request->customers_club_scores;
        foreach ($data as $item) {
            $r = CustomersClubGetScore::find($item['id']);
            $r->title = $item['title'];
            $r->score_value = $item['score_value'];
            $r->bon_value = $item['bon_value'];
            $r->save();
        }
        ActivityLogHelper::updatedModel('امتیازات با موفقیت به روزرسانی شد.', $r);

        if (request()->header('Accept') == 'application/json') {
            return $this->getClubScores();
        }
        return redirect()->route('admin.customersClub.getClubScores')
        ->with('success', 'امتیازات با موفقیت به روزرسانی شد.');
    }
    public function getUserLevels()
    {
        $list = CustomersClubLevel::select('id','title','min_score','max_score','color','permanent_purchase_discount','birthdate_discount','free_shipping')->get();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست سطوح مشتریان', compact('list'));
        }
        return view('customersclub::admin.setting.level', compact('list'));
    }

    public function setUserLevels(Request $request)
    {
        $data = $request->customers_club_user_levels;
        foreach ($data as $item) {
            $r = CustomersClubLevel::find($item['id']);
            $r->title = $item['title'];
            $r->min_score = $item['min_score'];
            $r->max_score = $item['max_score'];
            $r->color = $item['color'];
            $r->permanent_purchase_discount = $item['permanent_purchase_discount'];
            $r->birthdate_discount = $item['birthdate_discount'];
            $r->free_shipping = $item['free_shipping'];
            $r->save();
        }
        ActivityLogHelper::updatedModel('اطلاعات با موفقیت به روزرسانی شد.', $r);

        if (request()->header('Accept') == 'application/json') {
            return $this->getUserLevels();
        }
        return redirect()->route('admin.customersClub.getUserLevels')
        ->with('success', 'اطلاعات با موفقیت به روزرسانی شد.');
    }

    public function getPurchaseScores()
    {
        $list = CustomersClubSellScore::select('id','title','min_value','max_value','bon_value','score_value')->orderBy('min_value')->get();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست امتیازات خرید توسط مشتریان', compact('list'));
        }
        return view('customersclub::admin.setting.purchaseScores', compact('list'));
    }

    public function setPurchaseScore(Request $request)
    {
        $data = $request->customers_club_purchase_scores;  

        foreach ($data as $item) {  
            $minValue = str_replace(',', '', $item['min_value']);  
            $maxValue = str_replace(',', '', $item['max_value']);  
            $bonValue = str_replace(',', '', $item['bon_value']);  
            $scoreValue = str_replace(',', '', $item['score_value']);  
        
            $r = CustomersClubSellScore::find($item['id']);  
            $r->title = $item['title'];  
            $r->min_value = $minValue;  
            $r->max_value = $maxValue;  
            $r->bon_value = $bonValue;  
            $r->score_value = $scoreValue;  
            $r->save();  
        }
        ActivityLogHelper::updatedModel('امتیازات خرید با موفقیت به روزرسانی شد.', $r);

        if (request()->header('Accept') == 'application/json') {
            return $this->getPurchaseScores();
        }
        return redirect()->route('admin.customersClub.getPurchaseScores')
        ->with('success', 'امتیازات خرید با موفقیت به روزرسانی شد.');
    }

    public function addPurchaseScore(Request $request)
    {
        $request->merge([
            'min_value' => str_replace(',', '', $request->input('min_value')),
            'max_value' => str_replace(',', '', $request->input('min_value')),
            'bon_value' => str_replace(',', '', $request->input('bon_value')),
            'score_value' => str_replace(',', '', $request->input('score_value')),
        ]);
        $r = new CustomersClubSellScore();
        $r->title = $request->title;
        $r->min_value = $request->min_value;
        $r->max_value = $request->max_value;
        $r->bon_value = $request->bon_value;
        $r->score_value = $request->score_value;
        $r->save();

        return $this->getPurchaseScores();
    }

    public function deletePurchaseScore($id)
    {
        $customersClubSellScore = CustomersClubSellScore::find($id)->delete();  

        return redirect()->route('admin.customersClub.getPurchaseScores')
        ->with('success', 'امتیازات خرید با موفقیت حذف شدند.');
        // CustomersClubSellScore::find($request->id)->delete();
        // return $this->getPurchaseScores();
    }

    public function getBonValues()
    {
        $list = CustomersClubSetting::select('id','value','date')->where('key','benedi_bon')->orderBy('id','desc')->get();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست قیمت های تعیین شده برای هر بن', compact('list'));
        }
        return view('customersclub::admin.setting.bonValue', compact('list'));
    }

    public function addBonValue(Request $request)
    {
        $request->merge([
            'value' => str_replace(',', '', $request->input('value')),
        ]);
        $r = new CustomersClubSetting();
        $r->key = 'benedi_bon';
        $r->value = $request->value;
        $r->date = date('Y-m-d');
        $r->type = 'number';
        $r->save();
        ActivityLogHelper::storeModel('بن ثبت شد', $r);

        if (request()->header('Accept') == 'application/json') {
            return $this->getBonValues();
        }
        return redirect()->route('admin.customersClub.getBonValues')
        ->with('success', 'بن با موفقیت ثبت شد.');
    }

    public function getBonConvertRequestAdminList()
    {
        $list = CustomersClubBonConvertRequest::orderBy('id','desc')->paginate(50);

        $list->each(function ($item) {
            $item->makeHidden('customer');
            $item->makeHidden('created_at');
            $item->makeHidden('updated_at');
        });

        if (request()->header('Accept') == 'application/json') {
            return response()->success('لیست همه درخواست های تبدیل بن به هدیه کیف پول کاربران',compact('list'));
        }
        return view('customersclub::admin.exchangeBon.index', compact('list'));
    }

    public function updateBonConvertRequest(BonConvertRequestUpdate $request)
    {
        $bcr = CustomersClubBonConvertRequest::find($request->id);
        $bcr->makeHidden('customer');
        $bcr->makeHidden('created_at');
        $bcr->makeHidden('updated_at');

        if ($bcr->status == 'new'){
            try {

                \DB::beginTransaction();

                if (strlen($request->description) > 0){
                    $description = $request->description;
                } else {
                    $description = $request->status == 'approved' ? 'تأیید درخواست تبدیل بن به هدیه کیف پول' : 'درخواست شما تأیید نشد';
                }

                $lastBonValue = CustomersClubSetting::where('key','benedi_bon')->latest('id')->value('value');
                $converted_gift_value = $bcr->requested_bon * $lastBonValue;

                $bcr->status = $request->status;
                $bcr->action_date = date('Y-m-d');
                $bcr->description = $description;
                if ($request->status=='approved'){

                    $wallet = Wallet::where('holder_type','Modules\Customer\Entities\Customer')->where('holder_id',$bcr->customer_id)->first();
                    $wallet->gift_balance += $converted_gift_value;
                    $wallet->balance += $converted_gift_value;
                    $wallet->save();

                    $customers_club_gift_id = DB::table('charge_types')->where('value','customers_club_gift')->value('id');

                    $transaction = new Transaction();
                    $transaction->payable_type = 'Modules\Customer\Entities\Customer';
                    $transaction->payable_id = $bcr->customer_id;
                    $transaction->wallet_id = $wallet->id;
                    $transaction->type = 'deposit';
                    $transaction->amount = $converted_gift_value;
                    $transaction->confirmed = 1;
                    $transaction->charge_type_id = $customers_club_gift_id;
                    $transaction->meta = ['description' => $description];
                    $transaction->uuid = Str::uuid()->toString();
                    $transaction->save();

                    $bcr->converted_gift_value = $converted_gift_value;
                    $bcr->transaction_id = $transaction->id;

                    // ثبت بن منفی برای کم کردن تعداد بن کاربر
                    $customer_club_score = new CustomersClubScore();
                    $customer_club_score->customer_id = $bcr->customer_id;
                    $customer_club_score->cause_id = null;
                    $customer_club_score->cause_title = "درخواست تبدیل بن کاربر به هدیه کیف پول ($bcr->requested_bon بن = $converted_gift_value تومان)";
                    $customer_club_score->score_value = 0;
                    $customer_club_score->bon_value = (-1) * $bcr->requested_bon;
                    $customer_club_score->date = date('Y-m-d');
                    $customer_club_score->status = 1;

                    $customer_club_score->save();
                }
                $bcr->save();

                \DB::commit();

                if (request()->header('Accept') == 'application/json') {
                    return response()->success('وضعیت درخواست با موفقیت تغییرکرد',compact('bcr'));
                }
                return redirect()->back()
                ->with('success', 'وضعیت درخواست با موفقیت تغییرکرد.');
            } catch (\Error $exception) {

                if (DB::getRawPdo()->inTransaction()) {
                    \DB::rollBack();
                }

                if (request()->header('Accept') == 'application/json') {
                    return response()->success('خطایی هنگام انجام عملیات رخ داد');
                }
                return redirect()->back()
                ->with('error', 'خطایی هنگام انجام عملیات رخ داد.');
            }
        } else {
            if (request()->header('Accept') == 'application/json') {
                return response()->success('فقط درخواست های جدید قابلیت تغییر وضعیت را دارند', compact('bcr'));
            }
            return redirect()->back()
            ->with('error','فقط درخواست های جدید قابلیت تغییر وضعیت را دارند');
        }
    }

    public function getBirthdateSettings()
    {
        $days_birth_date_discount_active = CustomersClubSetting::select('id','value','date')->where('key','days_birth_date_discount_active')->value('value');
        $max_birth_date_discount_usage = CustomersClubSetting::select('id','value','date')->where('key','max_birth_date_discount_usage')->value('value');
        $birth_date_settings = [
            'days_birth_date_discount_active' => $days_birth_date_discount_active,
            'max_birth_date_discount_usage' => $max_birth_date_discount_usage,
        ];

        if (request()->header('Accept') == 'application/json') {
            return response()->success('تنظیمات تخفیف تولد', compact('birth_date_settings'));
        }
        return view('customersclub::admin.setting.birthdate', compact('birth_date_settings'));
    }

    public function setBirthdateSettings(Request $request)
    {
        $rules = [
            'days_birth_date_discount_active' => 'required',
            'max_birth_date_discount_usage' => 'required'
        ];
        $request->validate($rules);

        $days_setting = Helpers::getCustomersClubSettingByKey('days_birth_date_discount_active');  // دریافت مقدار تعداد روزهای مجاز استفاده از کد تخفیف
        $days_setting = CustomersClubSetting::find($days_setting->id);
        $days_setting->update([
            'value' => $request->days_birth_date_discount_active
        ]);

        $usage_setting = Helpers::getCustomersClubSettingByKey('max_birth_date_discount_usage');  // دریافت مقدار حداکثر تعداد استفاده از کد تخفیف
        $usage_setting = CustomersClubSetting::find($usage_setting->id);
        $usage_setting->update([
            'value' => $request->max_birth_date_discount_usage
        ]);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('با موفقیت به روز شد',[
                'birth_date_settings' => [
                    'days_birth_date_discount_active'=>$days_setting->value ,
                    'max_birth_date_discount_usage'=>$usage_setting->value ,
                ]
            ]);
        }
        return redirect()->route('admin.customersClub.getBirthdateSettings')
        ->with('success', 'اطلاعات با موفقیت به روزرسانی شد.');
    }

//    public function testDiscount()
//    {
//        $data = (new \Modules\CustomersClub\Helpers\Helpers)->generateDiscountCodeForBirthDate();
//        return $data;
//    }

//    public function testDuplicateScore()
//    {
//        $list_ids = CustomersClubScore::query()
//            ->select(
//                'id',
//                DB::raw('count(*) as count')
//            )
//            ->groupBy(['customer_id', 'cause_id', 'cause_title', 'date'])
//            ->having(DB::raw('count'),'>',1)
//            ->pluck('id');
//        dd($list_ids);
//
//    }
}
