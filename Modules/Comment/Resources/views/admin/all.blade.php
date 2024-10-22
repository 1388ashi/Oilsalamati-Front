@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست نظرات مطالب']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">نظرات مطالب ({{ $totalComments }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>نظر</th>
                        <th>نام کاربر</th>
                        <th>ایمیل</th>
                        <th>مطلب</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>پاسخ</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($comments as $comment)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td style="white-space: wrap">{{ $comment->body }}</td>
                            <td>{{ $comment->name ?? '-' }}</td>
                            <td>{{ $comment->email ?? '-' }}</td>
                            <td style="white-space: wrap">{{ $comment->commentable->title }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot name="type">{{ config('comment.status_color.' . $comment->status) }}</x-slot>
                                    <x-slot name="text">{{ config('comment.statuses.' . $comment->status) }}</x-slot>
                                </x-badge>
                            </td>
                            <td>{{ verta($comment->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @php($isDisabled = $comment->parent_id || $comment->children->isNotEmpty() ? true : false)
                                <button class="btn btn-sm btn-icon btn-purple text-white position-relative"
                                    @if (!$isDisabled) data-target="#showCommentAnswerModal-{{ $comment->id }}" 
                  data-toggle="modal"
                @else
                  disabled="disabled" @endif
                                    style="padding: 1px 6px;">
                                    <i class="fe fe-message-circle"></i>
                                    @if ($comment->children->isNotEmpty())
                                        <span
                                            class="font-weight-bold text-white fs-10 d-flex align-items-center justify-content-center position-absolute "
                                            style="
                      content:\2713;
                      background-color: #00d92a;
                      top: -11px;
                      right: -8px;
                      width: 18px;
                      height: 18px;
                      border-radius: 50px;">
                                            &#10003;
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td>

                                @can('read_comment')
                                    @include('core::includes.show-icon-button', [
                                        'model' => $comment,
                                        'route' => 'admin.post-comments.show',
                                    ])
                                @endcan

                                @can('modify_comment')
                                    @include('core::includes.edit-modal-button', [
                                        'target' => '#editCommentModal-' . $comment->id,
                                    ])
                                @endcan

                                @can('delete_comment')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $comment,
                                        'route' => 'admin.post-comments.destroy',
                                    ])
                                @endcan

                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">
                    {{ $comments->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @if ($comments->isNotEmpty())
        @include('comment::admin.includes.edit-modal')
        @include('comment::admin.includes.answer-modal')
    @endif
@endsection
