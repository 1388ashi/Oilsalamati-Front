<?php

namespace Modules\Product\Services\Validations;

//use Shetabit\Shopit\Modules\Product\Services\Validations\ProductAttributeValidationService as BaseProductAttributeValidationService;

use Modules\Core\Classes\SafeArray;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeValue;
use Modules\Product\Entities\Variety;
use Modules\Specification\Entities\Specification;
use Modules\Product\Services\Validations\DiscountValidationService;

class ProductAttributeValidationService
{
    protected $varieties = [];
    protected $product;

    public function __construct($varieties, $product = [])
    {
        $this->product = $product;
        $this->varieties = $varieties;
        $this->checkAttributes();
    }

    public function checkAttributes()
    {
        foreach ($this->varieties as $variety) {
            $variety = new SafeArray($variety);

            if($variety['discount_type'] != null) {
                (new DiscountValidationService(
                    $variety['discount_type'], new Variety,
                    $variety['discount'], $variety['price']))
                    ->checkDiscount();
            }
            if($variety['discount_type'] == null && $this->product['discount_type'] != null){
                (new DiscountValidationService(
                    $this->product['discount_type'],new Variety,
                    $this->product['discount'], $variety['price']))
                    ->checkDiscount();
            }


            $attribute = $variety['attributes'] ?? false;

            if ($attribute){

                $attributeIds = collect($attribute)->pluck('id');

                $attributeModels = Attribute::
                whereIn('id', $attributeIds)
                    ->orderByRaw('FIELD(id, '.implode(", " , $attributeIds->toArray()).')')
                    ->get();

                if (count($attribute) != count($attributeModels)) {
                    throw Helpers::makeValidationException('تعداد ويزگی های وارد شده نامعتبر است');
                }

                foreach ($attribute as $key => $attributeFromRequest) {
                    $currentAttributeModel = $attributeModels[$key];
                    $this->checkSelectAttribute($currentAttributeModel ,$attributeFromRequest);
                    $this->checkTextAttribute($currentAttributeModel ,$attributeFromRequest);
                }
            }
        }
    }

    public function checkSelectAttribute($currentAttributeModel ,$attributeFromRequest)
    {
        $value = $attributeFromRequest['value'] ?: false;
        if ($currentAttributeModel->type == Attribute::TYPE_SELECT) {
            if (isset($value)
                && !is_numeric($value)
            ) {
                throw Helpers::makeValidationException('مقدار ویژگی ' . $currentAttributeModel->name . ' باید عدد باشد. ');
            }

            $specificationSelectExists = AttributeValue::where('attribute_id', $currentAttributeModel->id)
                ->where('id', $value)->exists();

            if (!$specificationSelectExists) {
                throw Helpers::makeValidationException("مقدار وارد شده برای ویژگی " . $currentAttributeModel->name . " نامعتبر می باشد ");
            }
        }
    }

    public function checkTextAttribute($currentAttributeModel ,$attributeFromRequest)
    {
        if ($currentAttributeModel->type == Specification::TYPE_TEXT) {
            if (isset($attributeFromRequest['value'])
                && !is_string($attributeFromRequest['value'])
            ) {
                throw Helpers::makeValidationException('مقدار ویژگی ' . $currentAttributeModel->name . ' باید متن باشد. ');
            }
        }
    }
}
