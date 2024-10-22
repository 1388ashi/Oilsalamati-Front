<?php

use Modules\Specification\Entities\Specification;

$typeText = Specification::TYPE_TEXT;
$typeSelect = Specification::TYPE_SELECT;
$typeMultiSelect = Specification::TYPE_MULTI_SELECT;

return [

	'name' => 'Specification',

	'types' => [
		$typeText => 'متنی',
		$typeSelect => 'تک مقدار',
		$typeMultiSelect => 'چند مقدار'
	]

];