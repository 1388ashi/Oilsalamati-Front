<?php

namespace Modules\Product\Services\Validations;

//use Shetabit\Shopit\Modules\Product\Services\Validations\ProductSpecificationValidationService as BaseProductSpecificationValidationService;

use Modules\Category\Entities\Category;
use Modules\Core\Helpers\Helpers;
use Modules\Specification\Entities\Specification;
use Modules\Specification\Entities\SpecificationValue;

class ProductSpecificationValidationService
{
    public $specificationModels = [];
    protected $specifications = [];

    public function __construct($specifications, public $categories = [])
    {
        $this->specifications = $specifications;
        $this->checkSpecifications();
    }

    public function checkSpecifications()
    {

        $specification = $this->specifications ?: false;

        if ($specification) {

            $specificationIds = collect($specification)->pluck('id');
            $specificationModels = Specification::query()
                ->whereIn('id', $specificationIds)
                ->orderByRaw('FIELD(`id`, ' . implode(", ", $specificationIds->toArray()) . ')')
                ->get();

            $specificationModelIds = $specificationModels->pluck(['id'])->toArray();
            /** @var Category $category */
            if (!empty($this->categories)) {
                $categories = Category::query()->findMany($this->categories);

                foreach ($categories as $category) {
                    $specifications = $category->specifications->where('required', true);
                    foreach ($specifications ?? [] as $_specification) {
                        if (!in_array($_specification->id, $specificationModelIds)) {
                            throw Helpers::makeValidationException("وارد کردن مشخصه '{$_specification->name}' اجباری است");
                        }
                    }
                }
            }


            if (count($specification) != count($specificationModels)) {
                throw Helpers::makeValidationException('تعداد مشخصات وارد شده نامعتبر است');
            }

            foreach ($specification as $key => $specificationFromRequest) {
                $currentSpecificationModel = $specificationModels[$key];
                $this->checkMultiSelectSpecification($currentSpecificationModel, $specificationFromRequest);
                $this->checkSelectSpecification($currentSpecificationModel, $specificationFromRequest);
                $this->checkTextSpecification($currentSpecificationModel, $specificationFromRequest);
            }
        }
    }

    public function checkMultiSelectSpecification($currentSpecificationModel, $specificationFromRequest)
    {
        if (array_key_exists('value', $specificationFromRequest)) {  
            $values = $specificationFromRequest['value'];
            if ($currentSpecificationModel->type == Specification::TYPE_MULTI_SELECT) {
                if (isset($values) && !is_array($values)) {
                    throw Helpers::makeValidationException('مقدار مشخصات ' . $currentSpecificationModel->name . ' باید آرایه باشد. ');
                }
                if (isset($values) && !is_array($values)) {
                    $specificationMultiSelectCount = SpecificationValue::where('specification_id', $currentSpecificationModel->id)
                    ->whereIn('id', $values)->count();
                    if ($specificationMultiSelectCount !== count($values)) {
                        throw Helpers::makeValidationException(" نامعتبر می باشد" . $currentSpecificationModel->name . "مقدار های وارد شده برای مشخصه ");
                    }
                }
            }
        }
    }

    public function checkSelectSpecification($currentSpecificationModel, $specificationFromRequest)
    {
        if (array_key_exists('value', $specificationFromRequest)) {     
            $value = $specificationFromRequest['value'];
        }else{
            $value = false;
        }
        if ($currentSpecificationModel->type == Specification::TYPE_SELECT) {
            if (isset($value)
                && !is_numeric($value)
            ) {
                throw Helpers::makeValidationException('مقدار مشخصه ' . $currentSpecificationModel->name . ' باید عدد باشد. ');
            }

            $specificationSelectExists = SpecificationValue::where('specification_id', $currentSpecificationModel->id)
                ->where('id', $value)->exists();

            if (!$specificationSelectExists) {
                throw Helpers::makeValidationException("مقدار وارد شده برای مشخصه" . $currentSpecificationModel->name . " نامعتبر می باشد ");
            }

        }
    }

    public function checkTextSpecification($currentSpecificationModel, $specificationFromRequest)
    {
        if ($currentSpecificationModel->type == Specification::TYPE_TEXT) {
            if (isset($specificationFromRequest['value'])
                && !is_string($specificationFromRequest['value'])
            ) {
                throw Helpers::makeValidationException('مقدار مشخصه ' . $currentSpecificationModel->name . ' باید متن باشد. ');
            }
        }
    }
}
