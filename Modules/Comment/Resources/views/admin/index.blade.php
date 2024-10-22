@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">لیست مطالب</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts.show', $post) }}">نمایش جزییات مطلب</a></li>
            <li class="breadcrumb-item active">نظرات مطلب</li>
        </ol>
    </div>
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title"> نظرات مطلب : <strong>{{ $post->title }} ({{ $totalComments }})</strong> </p>
            <x-card-options />
        </div>
        <div class="card-body">
            @include('components.errors')
            <div class="table-responsive">
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <table
                            class="table table-vcenter text-center table-striped text-nowrap table-bordered border-bottom">
                            <thead>
                                <tr>
                                    <th>ردیف</th>
                                    <th>نظر</th>
                                    <th>نام کاربر</th>
                                    <th>ایمیل</th>
                                    <th>وضعیت</th>
                                    <th>تاریخ ثبت</th>
                                    <th>پاسخ</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comments as $comment)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $comment->body }}</td>
                                        <td>{{ $comment->name }}</td>
                                        <td>{{ $comment->email }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ config('comment.status_color.' . $comment->status) }}">
                                                {{ config('comment.statuses.' . $comment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ verta($comment->created_at)->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-icon btn-purple text-white"
                                                data-target="#showCommentAnswerModal" data-toggle="modal"
                                                style="padding: 1px 6px;">
                                                <i class="fe fe-message-circle"></i>
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

                                    @include('core::includes.data-not-found-alert', ['colspan' => 8])
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('comment::admin.includes.edit-modal')
    @include('comment::admin.includes.answer-modal')
@endsection
