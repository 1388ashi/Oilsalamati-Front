<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Rules\Base64Image;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Http\Requests\Customer\ChangePasswordRequest;
use Modules\Customer\Http\Requests\Customer\ProfileUpdateRequest;
//use Shetabit\Shopit\Modules\Admin\Http\Controllers\Admin\ProfileController as BaseProfileController;

class ProfileController extends Controller
{

    private null|\Illuminate\Contracts\Auth\Authenticatable|Customer $user;

    public function __construct()
{
    $this->middleware(function ($request, $next){
        $this->user = auth()->user();

        return $next($request);
    });
}

    public function edit()
{
    $admin = $this->user;

    return response()->success('دریافت اطلاعات پروفایل ادمین', compact('admin'));
}

    public function update(ProfileUpdateRequest $request)
{
    $admin = $this->user;

    $admin->fill($request->all());
    if ($request->filled('password')) {
        $admin->password = $request->input('password');
    }
    $admin->save();

    if($request->hasFile('image')){
        $admin->addImage($request->image);
    }

    return response()->success('پروفایل با موفقیت به روزرسانی شد', compact('admin'));
}

    public function changePassword(ChangePasswordRequest $request)
{
    $admin = $this->user;

    $admin->fill(['password' => $request->password])->save();

    return response()->success('کلمه عبور با موفقیت تغییر کرد.');
}

    public function uploadImage(Request $request)
{
    $request->validate([
        'image' => ['required','string', new Base64Image()]
    ]);

    $image = $this->user->addImage($request->image);

    return response()->success('عکس پروفایل با موفقیت ویرایش شد', compact('image'));
}
}
