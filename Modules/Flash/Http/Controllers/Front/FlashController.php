<?php

namespace Modules\Flash\Http\Controllers\Front;

//use Shetabit\Shopit\Modules\Flash\Http\Controllers\Front\FlashController as BaseFlashController;


use Illuminate\Routing\Controller;
use Modules\Flash\Entities\Flash;

class FlashController extends Controller
{
    public function index()
    {
        $flashes = Flash::active()->orderBy('order')->get([
            'title', 'timer'
        ]);

        return response()->success('', compact('flashes'));
    }
}
