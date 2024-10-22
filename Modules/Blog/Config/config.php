<?php

use Modules\Blog\Entities\Post;

$statusPublished = Post::STATUS_PUBLISHED;
$statusDraft = Post::STATUS_DRAFT;
$statusPending = Post::STATUS_PENDING;
$statusUnpublished = Post::STATUS_UNPUBLISHED;

return [
  'name' => 'Blog',
  'orderCacheTime' => 6000,

  'statuses' => [
    $statusPublished => 'انتشار یافته',
    $statusDraft => 'پیش نویس',
    $statusPending => 'در انتظار تایید',
    $statusUnpublished => 'منتشر نشده',
  ],

  'status_color' => [
    $statusPublished => 'success',
    $statusDraft => 'info',
    $statusPending => 'warning',
    $statusUnpublished => 'danger',
  ]
];
