<?php

namespace Modules\Customer\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Rules\Base64Image;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\Deposit;
use Modules\Customer\Http\Requests\Customer\ChangePasswordRequest;
use Modules\Customer\Http\Requests\Customer\ProfileUpdateRequest;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\Newsletters\Entities\UsersNewsletters;
//use Shetabit\Shopit\Modules\Customer\Http\Controllers\Customer\ProfileController as BaseProfileController;

class ProfileController extends Controller
{
    public function update(ProfileUpdateRequest $request)
    {
        $customer = auth()->user();

        $customer->fill($request->only([
            'first_name',
            'last_name',
            'email',
            'national_code',
            'gender',
            'card_number',
            'birth_date',
            'newsletter',
            'foreign_national'
        ]));
        if ($request->filled('password')) {
            $customer->password = $request->input('password');
        }
        if ($request->newsletter){
            UsersNewsletters::query()->firstOrCreate($request->only('email'));
        }else{
            $email = UsersNewsletters::query()->where('email', $request->email)->first();
            $email && $email->delete();
        }
        $customer->save();

        if($request->hasFile('image')){
            $customer->addImage($request->image);
        }


        $get_score = Helpers::getCustomersClubScoreByKey('complete_register_in_website');  // دریافت امتیازی که بابت تکمیل داده های کاربر به وی تعلق می گیرد

        $all_needed_data_sent = true;
        if (!$request->first_name) $all_needed_data_sent = false;
        if (!$request->last_name) $all_needed_data_sent = false;
        if (!$request->birth_date) $all_needed_data_sent = false;
        if (!$request->national_code) $all_needed_data_sent = false;
        if (!$request->gender) $all_needed_data_sent = false;
//        if (!$request->email) $all_needed_data_sent = false;
//        if (!$request->card_number) $all_needed_data_sent = false;

        if ($get_score && $all_needed_data_sent){
            // در صورتی که برای این مرحله امتیاز درنظر گرفته شده باشد و داده های موردنیاز کامل وارد شده باشد
            // ابتدا چک می شود که امتیاز داده شده یا نه و سپس درصورت ثبت نشده، امتیاز ثبت می گردد.

            $customer_club_score = CustomersClubScore::query()
                ->where('customer_id',$customer->id)
                ->where('cause_id', $get_score->id)
                ->first();

            // در صوتی که امتیاز این مرحله وجود داشته باشد دوباره امتیاز داده نمی شود
            if (!$customer_club_score){
                $customer_club_score = new CustomersClubScore();
                $customer_club_score->customer_id = $customer->id;
                $customer_club_score->cause_id = $get_score->id;
                $customer_club_score->cause_title = (new \Modules\Core\Helpers\Helpers)->generateCauseTitleByCauseId($get_score->id);
                $customer_club_score->score_value = $get_score->score_value;
                $customer_club_score->bon_value = $get_score->bon_value;
                $customer_club_score->date = date('Y-m-d');
                $customer_club_score->status = 1;

                $customer_club_score->save();
            }
        }

        return response()->success('پروفایل کاربری با موفقیت به روزرسانی شد', compact('customer'));
    }

    public function edit()
    {
        $customer = Customer::find((Auth::user()->id));

        return response()->success('دریافت اطلاعات پروفایل مشتری', compact('customer'));
    }




    // came from vendor ================================================================================================


    private null|\Illuminate\Contracts\Auth\Authenticatable|Customer $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next){
            $this->user = auth()->user();

            return $next($request);
        });
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $customer = $this->user;

        $customer->fill(['password' => $request->password])->save();

        return response()->success('کلمه عبور با موفقیت تغییر کرد.');
    }

    public function depositWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1000'
        ]);

        try {
            $deposit = Deposit::storeModel($request->amount);

            return $deposit->pay();
        } catch (Throwable $e) {
            Log::error($e->getTraceAsString());
            throw Helpers::makeValidationException('عملیات شارژ کیف پول ناموفق بود،لطفا دوباره تلاش کنید.'.$e->getMessage(), $e->getTrace());
        }
    }

    public function transactionsWallet(): \Illuminate\Http\JsonResponse
    {
        /** @var Customer $customer */
        $customer = auth()->user();
        $transactions = $customer->transactions()->latest();
        Helpers::applyFilters($transactions);
        $transactions = Helpers::paginateOrAll($transactions);

        return response()->success('گزارشات کیف پول شما', compact('transactions'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required','string', new Base64Image()]
        ]);

        $image = $this->user->addImage($request->image);

        return response()->success('عکس پروفایل با موفقیت ویرایش شد', compact('image'));
    }

    public function walletBalance()
    {
        /** @var Customer $user */
        $user = Auth::user();

        return $user->balance;
    }

}
