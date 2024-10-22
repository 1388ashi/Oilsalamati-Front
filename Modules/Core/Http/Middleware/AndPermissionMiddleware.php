<?php

namespace Modules\Core\Http\Middleware;

use Shetabit\Shopit\Modules\Core\Http\Middleware\AndPermissionMiddleware as BaseAndPermissionMiddleware;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AndPermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        if (app('auth')->guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);


        $user = app('auth')->guard($guard)->user();
        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                throw UnauthorizedException::forPermissions($permissions);
            }
        }

        return $next($request);
    }
}
