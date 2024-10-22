<?php

use Modules\Customer\Entities\Withdraw;

$statusPending = Withdraw::STATUS_PENDING;
$statusPaid = Withdraw::STATUS_PAID;
$statusCanceled = Withdraw::STATUS_CANCELED;

return [
    'name' => 'Customer',

    'withdraw_statuses' => [
		$statusPaid => 'پرداخت شده',
		$statusPending => 'در انتظار تکمیل',
		$statusCanceled => 'لغو شده',
	],
	
];
