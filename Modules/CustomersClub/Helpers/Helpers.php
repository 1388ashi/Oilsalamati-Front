<?php

namespace Modules\CustomersClub\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AccountingReport\Http\Controllers\ProductSellReportController;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Customer;
use Modules\CustomersClub\Entities\CustomersClubLevel;
use Modules\CustomersClub\Entities\CustomersClubSetting;
use Modules\Report\Entities\SellType;
use Shetabit\Shopit\Modules\Sms\Sms;

class Helpers
{
    public function generateDiscountCodeForBirthDate()
    {
        $tomorrow_month_day = date('m-d', strtotime('+1 day'));

        $days_birth_date_discount_active = CustomersClubSetting::select('id','value','date')->where('key','days_birth_date_discount_active')->value('value');
        $max_birth_date_discount_usage = CustomersClubSetting::select('id','value','date')->where('key','max_birth_date_discount_usage')->value('value');

        $start_date_of_discount = date('Y-m-d'/*, strtotime('+1 day')*/);
        $days = $days_birth_date_discount_active /*+ 1*/; // عدد یک یعنی از فردا شروع بشه (یک روز اضافه تر بشه)
        $end_date_of_discount = date('Y-m-d', strtotime("+$days day"));

        // لیست افرادی که فردا تولدشان است
        $customers = Customer::query()
            ->whereRaw(" birth_date like '%$tomorrow_month_day' ")
            ->select(
                'id',
                'first_name',
                'last_name',
                'mobile',
            )
            ->get();

        // آماده سازی موارد موردنیاز جهت تولید کد تخفیف و ارسال پیامک به مشتریان
        $final_customers = [];
        foreach ($customers as $customer) {
            $final_customers[] = [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'mobile' => $customer->mobile,
                'customers_club_score' => $customer->customers_club_score,
                'customers_club_bon' => $customer->customers_club_bon,
                'customers_club_level' => $customer->customers_club_level,
                'customers_club_level_id' => $customer->customers_club_level['id'],
                'birth_date_discount' => CustomersClubLevel::find($customer->customers_club_level['id'])->birthdate_discount,
            ];
        }

        foreach ($final_customers as $final_customer) {
            if ($final_customer['birth_date_discount'] > 0){

                $discount_code = $this->generateDiscountCode(6, true, false, true);

                $coupon = new Coupon();
                $coupon->title = 'تخفیف تولد ' . "{$final_customer['first_name']} {$final_customer['last_name']} ({$final_customer['mobile']})";
                $coupon->code = $discount_code;
                $coupon->start_date = $start_date_of_discount;
                $coupon->end_date = $end_date_of_discount;
                $coupon->type = 'percentage';
                $coupon->coupon_type = 'admin';
                $coupon->amount = $final_customer['birth_date_discount'];
                $coupon->usage_limit = $max_birth_date_discount_usage;
                $coupon->usage_per_user_limit = $max_birth_date_discount_usage;
                $coupon->creator_id = 1;
                $coupon->updater_id = 1;

                $coupon->save();

                $pattern = 'birthday-discount';
                Sms::pattern($pattern)
                    ->data([
                        'token' => str_replace(' ',' ',$final_customer['first_name']), // نام
                        'token2' => $final_customer['birth_date_discount'], // درصد تخفیف
                        'token3' => str_replace(' ',' ',$discount_code . " تا " . (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi($end_date_of_discount)), // کد تخفیف و مهلت استفاده

                    ])
                    ->to([$final_customer['mobile']])
//                    ->to(['09119691921'])
                    ->send();
            }
        }

//        $pattern = 'birthday-discount';
//        Sms::pattern($pattern)
//            ->data([
//                'token' => str_replace(' ',' ','سید صادق'), // نام
//                'token2' => '20', // درصد تخفیف
//                'token3' => str_replace(' ',' ','abcdef' . " تا " . (new \Modules\Core\Helpers\Helpers)->convertMiladiToShamsi('2024-04-06')), // کد تخفیف و مهلت استفاده`
//            ])
//            ->to(['09119691921'])
//            ->send();

        return response()->success('لیست افرادی که تولد آن ها فرداست', compact('final_customers'));
    }

    public function generateDiscountCode($characters, $use_lowercase = true, $use_uppercase = true, $use_numbers = true)
    {
        $lowercase = $use_lowercase?'abcdefghijklmnopqrstuvwxyz':'';
        $uppercase = $use_uppercase?'ABCDEFGHIJKLMNOPQRSTUVWXYZ':'';
        $numbers = $use_numbers?'0123456789':'';
        return substr(str_shuffle(str_repeat($lowercase.$uppercase.$numbers, $characters)), 0, $characters);
    }
}
