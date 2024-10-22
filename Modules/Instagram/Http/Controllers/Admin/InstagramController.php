<?php

namespace Modules\Instagram\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Instagram\Http\Controllers\Admin\InstagramController as BaseInstagramController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Instagram\Entities\Instagram;

class InstagramController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Instagram $instagram
     * @return JsonResponse
     */
    public function index(Instagram $instagram): JsonResponse
    {
        $instagram = Instagram::query()->paginateOrAll();
        if ($instagram->isEmpty()){
            $instagram = Instagram::getInstagramPosts();
        }

        return response()->success('', compact('instagram'));
    }

    public function update(Instagram $instagram): JsonResponse
    {
        $instagram = $instagram->getForceInstagramPosts();

        return response()->success('پست های اینستاگرام با موفقیت بروزرسانی شد', compact('instagram'));
    }

}
