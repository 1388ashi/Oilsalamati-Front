<?php

use Modules\ProductQuestion\Entities\ProductQuestion;

$statusPending = ProductQuestion::STATUS_PENDING;
$statusApproved = ProductQuestion::STATUS_APPROVED;
$statusRejected = ProductQuestion::STATUS_REJECTED;

return [
    'name' => 'ProductQuestion',

    'statuses' => [
		$statusApproved => 'تایید شده',
		$statusPending => 'در انتظار تایید',
		$statusRejected => 'رد شده',
	],

	'status_color' => [
		$statusApproved => 'success',
		$statusPending => 'warning',
		$statusRejected => 'danger',
	]
];
