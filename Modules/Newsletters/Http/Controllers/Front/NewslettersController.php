<?php

namespace Modules\Newsletters\Http\Controllers\Front;

//use Shetabit\Shopit\Modules\Newsletters\Http\Controllers\Front\NewslettersController as BaseNewslettersController;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Rules\IranMobile;
use Modules\Newsletters\Entities\UsersNewsletters;

class NewslettersController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $phoneNumberRules = [
            'unique:users_newsletters,phone_number',
            new IranMobile(),
        ];
        if (!$request->email) {
            $phoneNumberRules[] = 'required';
        }
        $request->validate([
            'email' => (!$request->phone_number ? 'required|' : '') . 'email|unique:users_newsletters,email',
            'phone_number' => $phoneNumberRules,
        ]);

        $user = UsersNewsletters::query()->create($request->only(['email', 'phone_number']));
        $m = $request->email ? 'ایمیل' : 'شماره تماس';

        return response()->success("$m شما با موفقیت به خبرنامه افزوده شد.", compact('user'));
    }


}
