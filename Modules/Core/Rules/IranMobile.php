<?php

namespace Modules\Core\Rules;

//use Shetabit\Shopit\Modules\Core\Rules\IranMobile as BaseIranMobile;

use Illuminate\Contracts\Validation\Rule;

class IranMobile implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // این رجکس اشتباهه
//        return preg_match('/^09(1[0-9]|9[0-2]|2[0-2]|0[1-5]|41|3[0,3,5-9])\d{7}$/', $value);
        return preg_match('/^09\d{9}$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.iran_mobile');
    }
}
