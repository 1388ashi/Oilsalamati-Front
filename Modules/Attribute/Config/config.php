<?php
use Modules\Attribute\Entities\Attribute;

$typeText = Attribute::TYPE_TEXT;
$typeSelect = Attribute::TYPE_SELECT;
return [
    'name' => 'Attribute',
    'types' => [
      $typeText => 'متنی',
      $typeSelect => 'انتخابی',
    ]
];
