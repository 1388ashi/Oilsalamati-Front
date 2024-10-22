<?php

namespace Modules\Slider\Http\Controllers\All;

//use Shetabit\Shopit\Modules\Slider\Http\Controllers\All\SliderController as BaseSliderController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\Helpers;
use Modules\Slider\Entities\Slider;
use Modules\Slider\Http\Requests\Admin\SliderStoreRequest;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::query()->latest()->get();

        return response()->success('', ['sliders' => $sliders]);
    }
}

