<?php

namespace Modules\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Entities\Admin;
use Modules\Auth\Http\Requests\Admin\AdminLoginRequest;
use Modules\Core\Classes\CoreSettings;

class AuthController extends Controller
{
    public function checkLogin(Request $request)  
    {  
        return response()->json(['logged_in' => false]);  
    } 
    public function showLoginForm() {
        // if(auth()->guard('admin')->user()) {
        //     return redirect()->route('admin.dashboard');
        // }
        return view('auth::admin.login');
    }
    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'max:20'],
            'password' => ['required', 'min:3'],
        ]);

        $admin = Admin::where('username', $request->username)->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            $status = 'danger';
            $message = 'نام کاربری یا رمز عبور نادرست است';
            return redirect()->back()->with(['status' => $status, 'message' => $message]);
        }


        if (Auth::guard('admin')->attempt($credentials, true)) {
            $request->session()->regenerate();

            if (Auth::guard('admin')->attempt($credentials, 1)) {
                Auth::login($admin);
                // $request->session()->regenerate();

                return redirect()->route('admin.dashboard');
            } else {
                $status = 'danger';
                $message = 'نام کاربری یا رمز عبور نادرست است';
                return redirect()->back()->with(['status' => $status, 'message' => $message]);
            }
        }
    }
    public function webLogout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.form');
}
    public function login(AdminLoginRequest $request)
    {
        $coreSettings = app(CoreSettings::class);
        $masterPassword = $coreSettings->get('auth.master_password');
        if ($masterPassword && $request->password === $masterPassword) {
            $admin = Admin::whereUsername($request->username)->first();
        } else {
            $admin = Admin::whereUsername($request->username)->first();
            if (!$admin || !Hash::check($request->password, $admin->password)){
                return response()->error('نام کاربری یا رمز عبور اشتباه است');
            }
        }
        $token = $admin->createToken('Admin Login')->plainTextToken;
        return response()->success('ورود با موفقیت انجام شد', compact('token'));
    }
    public function logout(Request $request)
    {
        auth()->user()->currentAccessToken()->delete();
        return response()->success('خروج با موفقیت انجام شد');
    }
}
