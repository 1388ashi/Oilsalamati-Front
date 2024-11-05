<?php

namespace Modules\Auth\Http\Controllers\Customer;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Modules\Auth\Http\Requests\Customer\CustomerLoginRequest;
use Modules\Auth\Http\Requests\Customer\CustomerLoginVerifyRequest;
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

class AuthController extends Controller
{
    public function webSendSms($mobile,$type = null)
    {
        if (env('APP_ENV') != 'local') {
            $result = event(new SmsVerify($mobile));

            return redirect()->route('webSendSms',$mobile);
            if ($result[0]['status'] != 200) {
                $status = 'danger';
                $message = 'ارسال کد فعال سازی ناموفق بود.لطفا دوباره تلاش کنید';
                return redirect()->back()->with(['status' => $status, 'message' => $message]);
            }
        } else {
            $smsToken = SmsToken::where('mobile', $mobile)->first();
            if ($smsToken) {
                $smsToken->update([
                    'expired_at' => Carbon::now()->addHours(24),
                    'verified_at' => now()
                ]);
            }else {
                SmsToken::create([
                    'mobile' => $mobile,
                    'token' => 1234, //random_int(10000, 99999),
                    'expired_at' => Carbon::now()->addHours(240),
                    'verified_at' => now()
                ]);
            }
        }
        return view('auth::front.sms', compact('mobile','type'));
    }
    public function webSendSmsRegister($mobile)
    {
        return view('auth::front.sms-register', compact('mobile'));
    }
    public function registerLogin(CustomerRegisterLoginRequest $request,$mobile = null)
    {
        $key = Setting::getFromName('smsBomberKey');
        $value = Setting::getFromName('smsBomberValue');

        if(!$mobile){
            if (!$request->has($key) || $request->{$key} != $value) {
                $status = 'danger';
                $message = 'خطا در تایید کد کپچا';
                return redirect()->back()->with(['status' => $status, 'message' => $message]);
            }
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

        $mobile = $mobile ?: $request->mobile;

        // try {
            $customer = Customer::where('mobile', $mobile)->first();
            if ($customer && !$customer->isActive()) {
                $status = 'danger';
                $message = 'حساب شما غیر فعال است. لطفا با پشتیبانی تماس حاصل فرمایید.';
                return redirect()->back()->with(['status' => $status, 'message' => $message]);
            }
            $status = ($customer && $customer->password) ? 'login' : 'register';
            if ($status === 'register') {
                if (env('APP_ENV') != 'local') {
                    $result = event(new SmsVerify($mobile));

                    return redirect()->route('webSendSmsRegister',$mobile);
                    if ($result[0]['status'] != 200) {
                        $status = 'danger';
                        $message = 'ارسال کد فعال سازی ناموفق بود.لطفا دوباره تلاش کنید';
                        return redirect()->back()->with(['status' => $status, 'message' => $message]);
                    }
                } else {
                    $smsToken = SmsToken::where('mobile', $mobile)->first();
                    if ($smsToken) {
                        $smsToken->update([
                            'expired_at' => Carbon::now()->addHours(240),
                            'verified_at' => now()
                        ]);
                    }else {
                        SmsToken::create([
                            'mobile' => $mobile,
                            'token' => 1234, //random_int(10000, 99999),
                            'expired_at' => Carbon::now()->addHours(240),
                            'verified_at' => now()
                        ]);
                    }
                    return redirect()->route('webSendSmsRegister',$mobile);
                }
            }else{
                // if (env('APP_ENV') != 'local') {
                //     $result = event(new SmsVerify($mobile));

                //     return redirect()->route('webSendSms',$mobile);
                //     if ($result[0]['status'] != 200) {
                //         $status = 'danger';
                //         $message = 'ارسال کد فعال سازی ناموفق بود.لطفا دوباره تلاش کنید';
                //         return redirect()->back()->with(['status' => $status, 'message' => $message]);
                //     }
                // } else {
                //     SmsToken::create([
                //         'mobile' => $mobile,
                //         'token' => 1234, //random_int(10000, 99999),
                //         'expired_at' => Carbon::now()->addHours(240),
                //         'verified_at' => now()
                //     ]);
                    return redirect()->route('customer.showLoginForm',$mobile);
            }
        // } catch(\Exception $exception) {
        //     Log::error($exception->getTraceAsString());
        //     return redirect()->back()->with(['status' => 'danger', 'message' => 'مشکلی در برنامه بوجود آمده است. لطفا با پشتیبانی تماس بگیرید:'. $exception->getMessage()]);
        // }
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
    public function register(CustomerRegisterRequest $request)
    {
        dd('hi');
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

        $request->session()->regenerate();  
        Auth::guard('customer')->login($customer);  
        
        Helpers::actingAs($customer);

        $warnings = CartFromRequest::addToCartFromRequest($request);
        $notificationService = new NotificationService($customer);

        // لازمه
        $customer->getBalanceAttribute();
        $customer = Customer::query()->whereKey($customer->id)->first();
        // $request->session()->regenerate();
        // Auth::login($customer);

        return view('auth::front.password',compact('customer'));
    }
    public function createPassword(Request $request){
        $customer = Customer::query()->where('mobile',$request->mobile)->first();
        $customer->update([
            'password' => bcrypt($request->password)
        ]);
        return redirect()->intended('/');
        // return redirect()->route('home');
        }
    public function showLoginForm($mobile){
        return view('auth::front.login',compact('mobile'));
    }
    public function webLogin(CustomerLoginVerifyRequest $request)
    {
        if ($request->has('smsToken')) {
            $request->smsToken->verified_at = now();
            $request->smsToken->save();
        }
        $customer = Customer::where('mobile',$request->mobile)->first();  
        if ($request->type == 'login' && $customer) {  
            return $this->handleLogin($request, $customer);  
        }  
        return redirect()->route('home');  
    }  
    
    protected function handleLogin(Request $request, Customer $customer)  
    {  
        $credentials = $request->validate([  
            'mobile' => 'required',  
            'password' => 'nullable',  
        ]);  
    
        if ($request->password && !Hash::check($request->password, $customer->password)) {  

            return $this->redirectWithMessage('موبایل یا رمز عبور نادرست است', 'danger');  
        }  
    
        $request->session()->regenerate();  
        Auth::guard('customer')->login($customer);  

        if ($request->forget_password == 1) {
            return redirect()->route('pageRestsPassword',$request->mobile);
        }
        return redirect()->route('home');
    }
    protected function redirectWithMessage($message, $status)
    {
        return redirect()->back()->with(['status' => $status, 'message' => $message]);
    }

    public function webRegisterLogin($mobile = null) {
        return view('auth::front.register-login',compact('mobile'));
    }
    public function webLogout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home','با موفقیت خارج شدید');
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
    public function webResetPassword($mobile){
        return view('auth::front.reset-password',compact('mobile'));
    }
    public function resetPassword(CustomerResetPasswordRequest $request)
    {
        if ($request->smsToken) {
            $smsToken = SmsToken::where('mobile', $request->input('mobile'))->first();
            if ($smsToken->token !== $request->input('sms_token')) {
                throw Helpers::makeValidationException('توکن اشتباه است مججدا نلاش کنید');
            }
        }
        $customer = Customer::where('mobile', $request  ->mobile)->first();
        $customer->password = Hash::make($request->password);

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
        return redirect()->intended('/');
        // return redirect()->route('home');
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
