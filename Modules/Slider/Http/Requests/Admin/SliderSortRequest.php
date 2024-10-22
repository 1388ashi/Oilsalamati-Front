<?php

namespace Modules\Slider\Http\Requests\Admin;

//use Shetabit\Shopit\Modules\Slider\Http\Requests\Admin\SliderSortRequest as BaseSliderSortRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Helpers\Helpers;
use Modules\Slider\Entities\Slider;

class SliderSortRequest extends FormRequest
{
    private mixed $sliders;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'orders' => 'required|array',
            'group' => 'required'
        ];
    }

    protected function passedValidation()
    {
        // $countInput = count($this->input('orders'));
        // $sliders = Slider::whereGroup($this->input('group'))->get();
        // if ($countInput !== $sliders->count()) {
        //     throw Helpers::makeValidationException('داده ها اشتباه است');
        // }
        // $idsFromRequest = $this->input('orders');
        // foreach ($sliders as $slider) {
        //     if (!in_array($slider->id, $idsFromRequest)) {
        //         throw Helpers::makeValidationException('داده های ورودی اشتباه است. آیدی روبرو وجود ندارد: ' . $slider->id);
        //     }
        // }
        // $this->sliders = $sliders;
    }

}
