<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Customer;
use Shetabit\Shopit\Modules\Core\Helpers\Helpers;

class CheckUserStatus
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (($user instanceof Customer) && !$user->isActive()) {
            throw Helpers::makeValidationException('حساب شما غیر فعال است. لطفا با پشتیبانی تماس حاصل فرمایید.');
        }

        return $next($request);
    }
}
