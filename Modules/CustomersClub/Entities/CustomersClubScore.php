<?php

namespace Modules\CustomersClub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;

/**
 * @property mixed $customer_id
 * @property mixed $cause_id
 * @property mixed $score_value
 * @property mixed $bon_value
 * @property mixed|string $date
 * @property int|mixed $status
 * @property mixed $cause_title
 * @property mixed $product_id
 * @property mixed $extra_customer_id
 */
class CustomersClubScore extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $appends = [
        'cause',
        'product',
        'customer',
        'extra_customer',
        'mobile',
        'dashed_mobile',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function newFactory()
    {
        return \Modules\CustomersClub\Database\factories\CustomersClubScoresFactory::new();
    }

    public function getCauseAttribute()
    {
        return $this->cause_id?CustomersClubGetScore::find($this->cause_id)->title:'';
    }

    public function getProductAttribute()
    {
        return $this->product_id?Product::find($this->product_id)->title:'';
    }

    public function getCustomerAttribute()
    {
        return $this->customer_id?Customer::find($this->customer_id)->first_name . ' ' . Customer::find($this->customer_id)->last_name :'';
    }

    public function getExtraCustomerAttribute()
    {
        return $this->extra_customer_id?Customer::find($this->extra_customer_id)->first_name . ' ' . Customer::find($this->extra_customer_id)->last_name :'';
    }

    public function getMobileAttribute()
    {
        return Customer::find($this->customer_id)->mobile;
    }

    public function getDashedMobileAttribute()
    {
        return (new \Modules\Core\Helpers\Helpers)->convertMobileNumberForReport(Customer::find($this->customer_id)->mobile);
    }

    public function scopeSearchBetweenTwoDate($query)
    {
        $startDate = \request('from_date');
        $endDate = \request('to_date');
        return $query
            ->when($startDate & $endDate, function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('created_at', [$startDate, $endDate]);
            });
    }


    public static function insertOrderScores($order)
    {
        $amount_of_order = $order->total_invoices_amount - $order->total_shipping_amount;
        $score = \Modules\CustomersClub\Entities\CustomersClubSellScore::query()
            ->where('min_value','<=',$amount_of_order)
            ->where('max_value','>',$amount_of_order)
            ->first();


        if($score){
            // امتیاز دریافت شده در مرحله خرید محاسبه شده و برای مشتری ثبت می گردد
            $customer_club_score = new CustomersClubScore();
            $customer_club_score->customer_id = $order->customer_id;
            $customer_club_score->cause_id = null;
            $customer_club_score->cause_title = (new \Modules\Core\Helpers\Helpers)->generateCauseTitleBySellScoreId($score->id, $order->id);
            $customer_club_score->score_value = $score->score_value;
            $customer_club_score->bon_value = $score->bon_value;
            $customer_club_score->date = date('Y-m-d');
            $customer_club_score->order_id = $order->id;
            $customer_club_score->status = 1;

            $customer_club_score->save();

            $get_setting = Helpers::getCustomersClubSettingByKey('min_first_order');  // دریافت مقدار حداقل میزان اولین خرید برای ثبت امتیاز برای معرف
            if ($amount_of_order >= $get_setting->value){
                // یعنی مبلغ خرید کاربر از میزان مشخص شده برای حداقل خرید بیشتر است
                $reprezentant_id = DB::table('customers')->where('id',$order->customer_id)->first()->reprezentant_id;

                if ($reprezentant_id){
                    $get_score = Helpers::getCustomersClubScoreByKey('first_successful_payment_of_registered_with_invite_link');  // دریافت امتیازی که بابت اولین خرید بیشتر مبلغ تعیین شده انجام می شود
                    $customer_club_score_for_reprezentant = CustomersClubScore::query()
                        ->where('customer_id',$reprezentant_id)
                        ->where('cause_id', $get_score->id)
                        ->where('extra_customer_id', $order->customer_id)
                        ->first();

                    // در صوتی که امتیاز این مرحله وجود داشته باشد دوباره امتیاز داده نمی شود
                    if (!$customer_club_score_for_reprezentant){
                        $customer_club_score_for_reprezentant = new CustomersClubScore();
                        $customer_club_score_for_reprezentant->customer_id = $reprezentant_id;
                        $customer_club_score_for_reprezentant->extra_customer_id = $order->customer_id;
                        $customer_club_score_for_reprezentant->cause_id = $get_score->id;
                        $customer_club_score_for_reprezentant->cause_title = (new \Modules\Core\Helpers\Helpers)->generateCauseTitleByCauseId($get_score->id) . " (کاربر ".$order->customer_id." - سفارش " . $order->id . ")";
                        $customer_club_score_for_reprezentant->score_value = $get_score->score_value;
                        $customer_club_score_for_reprezentant->bon_value = $get_score->bon_value;
                        $customer_club_score_for_reprezentant->date = date('Y-m-d');
                        $customer_club_score_for_reprezentant->status = 1;

                        $customer_club_score_for_reprezentant->save();
                    }
                }
            }
        } else {
            Log::info("------------------------------------------");
            Log::info($amount_of_order . " | " . $order->id);
            Log::info("------------------------------------------");
        }
    }

    public static function takeBackOrderScores($order)
    {
        $amount_of_order = $order->total_invoices_amount - $order->total_shipping_amount;
        $score = \Modules\CustomersClub\Entities\CustomersClubSellScore::query()
            ->where('min_value','<=',$amount_of_order)
            ->where('max_value','>',$amount_of_order)
            ->first();


        // delete Customer club score for this order
        DB::table('customers_club_scores')
            ->where('customer_id', $order->customer_id)
            ->where('order_id', $order->id)
            ->delete();


        $get_setting = Helpers::getCustomersClubSettingByKey('min_first_order');  // دریافت مقدار حداقل میزان اولین خرید برای ثبت امتیاز برای معرف
        if ($amount_of_order >= $get_setting->value){
            // یعنی مبلغ خرید کاربر از میزان مشخص شده برای حداقل خرید بیشتر است
            $reprezentant_id = DB::table('customers')->where('id',$order->customer_id)->first()->reprezentant_id;

            if ($reprezentant_id){
                $get_score = Helpers::getCustomersClubScoreByKey('first_successful_payment_of_registered_with_invite_link');  // دریافت امتیازی که بابت اولین خرید بیشتر مبلغ تعیین شده انجام می شود
                $customer_club_score_for_reprezentant = CustomersClubScore::query()
                    ->where('customer_id',$reprezentant_id)
                    ->where('cause_id', $get_score->id)
                    ->where('extra_customer_id', $order->customer_id)
                    ->first();

                // if we inserted customer club score to the reprezentant for this order we should take it.
                // for this, the count of active orders of this customer must be one.
                if ($customer_club_score_for_reprezentant && $order->customer->orders()->where('status', Order::ACTIVE_STATUSES)->count() == 1)
                    $customer_club_score_for_reprezentant->delete();
            }
        }
    }
}
