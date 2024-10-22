<?php

use Modules\Blog\Entities\Post;
use Modules\ProductComment\Entities\ProductComment;

$statusPending = ProductComment::STATUS_PENDING;
$statusApproved = ProductComment::STATUS_APPROVED;
$statusReject = ProductComment::STATUS_REJECT;

return [
    'name' => 'ProductComment',

    'statuses' => [
		$statusApproved => 'تایید شده',
		$statusPending => 'در انتظار تایید',
		$statusReject => 'رد شده',
	],

	'status_color' => [
		$statusApproved => 'success',
		$statusPending => 'warning',
		$statusReject => 'danger',
	]
];
