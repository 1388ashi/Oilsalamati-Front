<?php

use Modules\Blog\Entities\Post;
use Modules\Comment\Entities\Comment;

$statusPending = Comment::STATUS_PENDING;
$statusApproved = Comment::STATUS_APPROVED;
$statusUnapproved = Comment::STATUS_UNAPPROVED;

return [
	'model' => Comment::class,
	'commented_models' => [
		'posts' => Post::class
	],

	'statuses' => [
		$statusApproved => 'تایید شده',
		$statusPending => 'در انتظار تایید',
		$statusUnapproved => 'تایید نشده',
	],

	'status_color' => [
		$statusApproved => 'success',
		$statusPending => 'warning',
		$statusUnapproved => 'danger',
	]
];
