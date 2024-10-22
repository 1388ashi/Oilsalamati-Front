<?php

namespace Modules\Core\Http\Middleware;

//use Shetabit\Shopit\Modules\Core\Http\Middleware\DisableCorsPolicy as BaseDisableCorsPolicy;


use Closure;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisableCorsPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if ($request->isMethod('OPTIONS')){
            $response = Response::make();
        } else {
            $response = $next($request);
        }


        if($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse
            || $response instanceof BinaryFileResponse) {
            return $response;
        } else {


            return $response
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
        }

    }
}
