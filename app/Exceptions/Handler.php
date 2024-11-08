<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Entities\Admin;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;
use Symfony\Component\HttpFoundation\Response as ResponseCode;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // return $request->expectsJson()
        //     ? response()->error('لطفا ابتدا وارد شوید', null, 401)
        //     : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    public function render($request, Throwable $e)
    {
        if ($request->wantsJson()) {
            if ($e instanceof ModelNotFoundException) {
                return response()->error('مورد خواسته شده یافت نشد', ['Message' => $e->getMessage()], 404);
            }
            elseif ($e instanceof ValidationException) {
                // this is prepared for nuxt error:
                $message = implode('|', Arr::collapse($e->errors()));
                return response()->error($message, $message, ResponseCode::HTTP_UNPROCESSABLE_ENTITY);
            }
            elseif ($e instanceof ThrottleRequestsException) {
                return response()->error(' درخواست های شما بیش از حد مجاز است', [$e->getMessage()], 422);
            }
            elseif ($e instanceof UnauthorizedException) {
                return response()->error('شما مجوز لازم را ندارید.', [$e->getMessage()], 422);
            }
            elseif ($e instanceof PermissionDoesNotExist) {
                return response()->error('مجوز پیدا نشد.', [$e->getMessage()], 422);
            }
        }

        return parent::render($request, $e);
    }
}
