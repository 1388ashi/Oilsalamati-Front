<?php

namespace Modules\Auth\Http\Controllers\Customer;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Http\Requests\Customer\CustomerLoginRequest;
use Modules\Auth\Http\Requests\Customer\CustomerRegisterLoginRequest;
use Modules\Auth\Http\Requests\Customer\CustomerRegisterRequest;
use Modules\Auth\Http\Requests\Customer\CustomerResetPasswordRequest;
use Modules\Auth\Http\Requests\Customer\CustomerSendTokenRequest;
use Modules\Auth\Http\Requests\Customer\CustomerVerifyRequest;
use Modules\Cart\Classes\CartFromRequest;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\NotificationService;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\SmsToken;
use Modules\Customer\Events\SmsVerify;
use Modules\Newsletters\Entities\UsersNewsletters;
use Modules\Setting\Entities\Setting;
use Shetabit\Shopit\Modules\Auth\Http\Controllers\Customer\AuthController as BaseAuthController;
use Modules\Core\Rules\IranMobile;

class AuthController extends BaseAuthController
{

    public function showLoginForm() {
        return view('auth::front.login');
    }

    public function registerLogin(CustomerRegisterLoginRequest $request): JsonResponse
    {
        $key = Setting::getFromName('smsBomberKey');
        $value = Setting::getFromName('smsBomberValue');

        if (!$request->has($key) || $request->{$key} != $value) {
            throw Helpers::makeValidationException('خطا در تایید کد کپچا');
        }
//        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
//            'secret' => config('services.recaptcha.secret_key'),
//            'response' => $request->get('g-recaptcha-response'),
//            'remoteip' => $request->getClientIp(),
//        ]);
//
//        if (! $response->json('success')) {
//            //Error verifying reCAPTCHA, please try again.
//            throw ValidationException::withMessages(['g-recaptcha-response' => 'خطا در تأیید کپچا، لطفاً دوباره امتحان کنید']);
//        }


        try {
            $customer = Customer::where('mobile', $request->mobile)->first();
            if ($customer && !$customer->isActive()) {
                return response()->error('حساب شما غیر فعال است. لطفا با پشتیبانی تماس حاصل فرمایید.');
            }
            $status = ($customer && $customer->password) ? 'login' : 'register';
            if ($status === 'register') {
                if (env('APP_ENV') != 'local') {
                    $result = event(new SmsVerify($request->mobile));
                    if ($result[0]['status'] != 200) {
                        return response()->error('ارسال کدفعال سازی ناموفق بود.لطفا دوباره تلاش کنید', null);
                    }
                } else {
                    // در صورتی که از سیستم لوکال استفاده شود فقط توکن ایجاد می شود که مثدار آن 12345 است
                    SmsToken::create([
                        'mobile' => $request->mobile,
                        'token' => 12345, //random_int(10000, 99999),
                        'expired_at' => Carbon::now()->addHours(240),
                        'verified_at' => now()
                    ]);
                }
            }

            $mobile = $request->mobile;

            return response()->success('بررسی وضعیت ثبت نام مشتری' , compact('status', 'mobile'));
        } catch(Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->error(
                'مشکلی در برنامه بوجود آمده است. لطفا با پشتیبانی تماس بگیرید: ' . $exception->getMessage(),
                $exception->getTrace(),
                500
            );
        }
    }

    // If not registered use this - Second
    // If forget password use this
    public function sendToken(CustomerSendTokenRequest $request): JsonResponse
    {
        $key = Setting::getFromName('smsBomberKey');
        $value = Setting::getFromName('smsBomberValue');

        if (!$request->has($key) || $request->{$key} != $value) {
            throw Helpers::makeValidationException('خطا در تایید کد کپچا');
        }
//        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
//            'secret' => config('services.recaptcha.secret_key'),
//            'response' => $request->get('g-recaptcha-response'),
//            'remoteip' => $request->getClientIp(),
//        ]);
//
//        if (! $response->json('success')) {
//            //Error verifying reCAPTCHA, please try again.
//            throw ValidationException::withMessages(['g-recaptcha-response' => 'خطا در تأیید کپچا، لطفاً دوباره امتحان کنید']);
//        }


        try {
            $result = event(new SmsVerify($request->mobile));
            if ($result[0]['status'] != 200) {
                throw new Exception($result[0]['message']);
            }
            $mobile = $request->mobile;
            return response()->success('بررسی وضعیت ثبت نام مشتری', compact('mobile'));
        } catch(Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->error(
                'مشکلی در برنامه بوجود آمده است. لطفا با پشتیبانی تماس بگیرید: ' . $exception->getMessage(),
                $exception->getTrace(),
                422
            );
        }
    }

    public function verify(CustomerVerifyRequest $request): JsonResponse
    {
        try {
            $request->smsToken->verified_at = now();
            $request->smsToken->save();
            $data['mobile'] = $request->mobile;

            $customer = $request->customer;
            if ($request->type === 'login' && $customer) {
                $customer->load(['listenCharges', 'carts']);
                $token = $customer->createToken('authToken')->plainTextToken;
                $data['access_token'] = $token;
                $data['user'] = $customer;
                $data['token_type'] = 'Bearer';
                $notificationService = new NotificationService($customer);
                $data['notifications'] = [
                    'items' => $notificationService->get(),
                    'total_unread' => $notificationService->getTotalUnread()
                ];
                Helpers::actingAs($customer);
                $warnings = CartFromRequest::addToCartFromRequest($request);
                $data['cart_warnings'] = $warnings;
                $data['carts'] = $customer->carts;
            }

            return response()->success('', compact('data'));
        } catch(\Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->error(
                'مشکلی در برنامه بوجود آمده است. لطفا با پشتیبانی تماس بگیرید: ' . $exception->getMessage(),
                $exception->getTrace(),
                500
            );
        }
    }

    // Fourth
    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        /** @var Customer $customer */
        if (!($customer = Customer::query()->where('mobile', $request->mobile)->first())) {
            $customer = Customer::create($request->all());
            if (isset($request->reprezentant_mobile)){
                $reprezentant = DB::table('customers')->where('mobile',$request->reprezentant_mobile)->first();
                if ($reprezentant){
                    $customer->reprezentant_id = $reprezentant->id;
                    $customer->save();
                    (new \Modules\Core\Helpers\Helpers)->setScoreForRegisterCustomer($reprezentant->id,$customer->id);
                }
            }
        } else {
            if ($customer->password) {
                return response()->error('این شماره همراه قبلا انتخاب شده است');
            }
            $customer->password = $request->password;
            $customer->save();
        }
        if ($request->newsletter){
            UsersNewsletters::query()->firstOrCreate($request->only('email'));
        }

        $customer->load(['listenCharges', 'carts']);

        $token = $customer->createToken('authToken')->plainTextToken;

        Helpers::actingAs($customer);

        $warnings = CartFromRequest::addToCartFromRequest($request);
        $notificationService = new NotificationService($customer);

        // لازمه
        $customer->getBalanceAttribute();
        $customer = Customer::query()->whereKey($customer->id)->first();

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'cart_warnings' => $warnings,
            'user' => $customer,
            'carts' => $customer->carts()->get(),
            'notifications' => [
                'items' => $notificationService->get(),
                'total_unread' => $notificationService->getTotalUnread()
            ]
        ];

        return response()->success('ثبت نام با موفقیت انجام شد', compact('data'));
    }

    public function webLogin(Request $request)
    {  
        $request->merge([
            'password' => 123456
        ]);
        
        $credentials = $request->validate([
            'mobile' => ['required', 'digits:11', new IranMobile()],
            'password' => ['required', 'min:3']
        ]);

        $customer = Customer::where('mobile', $request->mobile)->first();
        if (!$customer /* || !Hash::check($request->password, $customer->password) */) {
            $status = 'danger';
            $message = 'نام کاربری یا رمز عبور نادرست است';
            return redirect()->back()->with(['status' => $status, 'message' => $message]);
        }

        if (Auth::guard('customer')->attempt($credentials, true)) {

            $request->session()->regenerate();
            Auth::login($customer);

            return redirect()->route('home');

        }else {
            $status = 'danger';
            $message = 'نام کاربری یا رمز عبور نادرست است';
            return redirect()->back()->with(['status' => $status, 'message' => $message]);
        }
    }




    // came from vendor ================================================================================================
    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $customer = $request->customer;

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->error('اطلاعات وارد شده اشتباه است.', [], 400);
        }

        $customer->load(['listenCharges', 'carts']);
        $token = $customer->createToken('authToken');
        $token->accessToken->device_token = $request->device_token;
        $token->accessToken->save();
        // اون ایدی کارت هایی که ساخته شده در کوکی رو تو دیتابیس میزاره
        Helpers::actingAs($customer);
        $warnings = CartFromRequest::addToCartFromRequest($request);
        $notificationService = new NotificationService($customer);

        $data = [
            'user' => $customer,
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'cart_warnings' => $warnings,
            'carts' => $customer->carts()->get(),
            'notifications' => [
                'items' => $notificationService->get(),
                'total_unread' => $notificationService->getTotalUnread()
            ]
        ];

        return response()->success('کاربر با موفقیت وارد شد.', compact('data'));

    }

    public function logout(Request $request): JsonResponse
    {
        /**
         * @var $user Customer
         */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->success('خروج با موفقیت انجام شد');
    }

    public function resetPassword(CustomerResetPasswordRequest $request): JsonResponse
    {
        $smsToken = SmsToken::where('mobile', $request->input('mobile'))->first();
        if ($smsToken->token !== $request->input('sms_token')) {
            throw Helpers::makeValidationException('توکن اشتباه است مججدا نلاش کنید');
        }
        $customer = $request->customer;
        $customer->update($request->only('password'));

        Helpers::actingAs($customer);
        $warnings = CartFromRequest::addToCartFromRequest($request);

        $token = $customer->createToken('authToken')->plainTextToken;
        $customer->load(['listenCharges']);

        $notificationService = new NotificationService($customer);

        $data = [
            'user' => $customer,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'cart_warnings' => $warnings,
            'carts' => $customer->carts,
            'notifications' => [
                'items' => $notificationService->get(),
                'total_unread' => $notificationService->getTotalUnread()
            ]
        ];

        return response()->success('', compact('data'));
    }

    public function setDeviceToken(Request $request)
    {
        /**
         * @var $user Customer
         */
        $user = auth()->user();
        $accessToken = $user->currentAccessToken();
        $accessToken->device_token = $request->device_token;
        $accessToken->save();
    }


}
