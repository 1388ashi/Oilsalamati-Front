<?php

namespace Modules\Advertise\Http\Requests\Advertise;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Advertise\Entities\Advertise;
use Modules\Core\Helpers\Helpers;
use Shetabit\Shopit\Modules\Advertise\Http\Requests\Advertise\UpdatePossibilityRequest as BaseUpdatePossibilityRequest;

class UpdatePossibilityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'banner_ids' => 'required',
            'banner_ids.*' =>'required|exists:advertisements,id',
            'banner_possibility' => 'required|array',
            'banner_possibility.*' => 'required|integer|min:0'
        ];
    }

    public function passedValidation()
    {
        $position_id = $this->route('position');
        $countAds = Advertise::query()->where('position_id' , $position_id)->count();
        $possibility = 0;

        $idCount = count($this->banner_ids);
        $possibilityCount = count($this->banner_possibility);

        if (!($countAds == $idCount && $countAds == $possibilityCount )){
            throw Helpers::makeValidationException('احتمال تمامی جایگاه ها باید وارد شود');
        }

        for ($i=0 ; $countAds > $i ; $i++ ) {
            if ($position_id != Advertise::query()->where('id', $this->banner_ids[$i])->first()->position_id) {
                throw Helpers::makeValidationException('احتمال تبلیغات شما به نادرستی وارد شده است');
            }
            $possibility += $this->banner_possibility[$i];
        }

        if ( $possibility !== 100) {
            throw Helpers::makeValidationException('جمع احتمال ها باید مساوی 100 باشد');
        }
    }
}
