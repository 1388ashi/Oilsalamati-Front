<?php

namespace Modules\Link\Services;

//use Shetabit\Shopit\Modules\Link\Services\LinkValidator as BaseLinkValidator;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Category\Entities\Category;
use Modules\Core\Helpers\Helpers;

class LinkValidator
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function validate()
    {
        if ($this->request->filled('linkable_type')) {
            $this->request->merge([
                'link' => null
            ]);
            if (!$this->request->has('linkable_id')) {
                $this->request->merge([
                    'linkable_id' => null
                ]);
            }
        }
        if (!$this->request->input('link') && ($type = $this->request->input('linkable_type'))) {
            if (!class_exists($type) && !str_contains($type, 'Custom')) {
                throw Helpers::makeValidationException('تایپ انتخاب شده موجود نیست', 'linkable_type');
            }

            if ($id = $this->request->input('linkable_id')) {
                $model = $type::find($id);
                /**
                 * @var $model Model
                 */
                if (!$model) {
                    throw Helpers::makeValidationException('مدل انتخاب شده وجود ندارد', 'linkable_id');
                }
            }
//            else {
//                throw Helpers::makeValidationException('ورودی ها اشتباه است. لطفا یک آیتم انتخاب کنید(منو)');
//            }
        } else {
            if (!$this->request->input('link')) {
                throw Helpers::makeValidationException('وارد کردن لینک الزامی است');
            }
            $this->request->merge([
                'linkable_type'=> null,
                'linkable_id' => null
            ]);
        }
    }
}
