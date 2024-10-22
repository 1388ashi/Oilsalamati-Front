@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست نظرات مطالب', 'route_link' => 'admin.post-comments.all'],
                ['title' => 'پاسخ های نظر'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle"><span class="font-weight-normal"> پاسخ نظر : </span>{{ $comment->body }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>نظر</th>
                        <th>نام کاربر</th>
                        <th>ایمیل</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>پاسخ</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($comment->children as $comment)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $comment->body }}</td>
                            <td>{{ $comment->name }}</td>
                            <td>{{ $comment->email }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot name="type">{{ config('comment.status_color.' . $comment->status) }}</x-slot>
                                    <x-slot name="text">{{ config('comment.statuses.' . $comment->status) }}</x-slot>
                                </x-badge>
                            </td>
                            <td>{{ verta($comment->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-icon btn-purple text-white"
                                    data-target="#showCommentAnswerModal" data-toggle="modal" style="padding: 1px 6px;">
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
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @foreach ($comment->children as $comment)
        <x-modal id="editCommentModal-{{ $comment->id }}" size="md">
            <x-slot name="title">ویرایش نظر کد - {{ $comment->id }}</x-slot>
            <x-slot name="body">
                <form action="{{ route('admin.post-comments.update', $comment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">

                        <div class="col-12">
                            <div class="form-group">
                                <label for="name" class="control-label">نام :</label>
                                <input type="text" id="name" class="form-control" name="name" required
                                    value="{{ old('name', $comment->name) }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="email" class="control-label">ایمیل:</label>
                                <input type="email" id="email" class="form-control" name="email" required
                                    value="{{ old('email', $comment->email) }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="body" class="control-label">نظر :<span
                                        class="text-danger">&starf;</span></label>
                                <textarea name="body" class="form-control" id="body" rows="2">{{ $comment->body }}</textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="label" class="control-label"> وضعیت: <span
                                        class="text-danger">&starf;</span></label>
                                <select name="status" id="status" class="form-control">
                                    @foreach (config('comment.statuses') as $name => $label)
                                        <option value="{{ $name }}"
                                            {{ $comment->status == $name ? 'selected' : null }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer justify-content-center mt-2">
                        <button class="btn btn-warning" type="submit">بروزرسانی</button>
                        <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
                    </div>

                </form>
            </x-slot>
        </x-modal>

        <x-modal id="showCommentAnswerModal-{{ $comment->id }}" size="md">
            <x-slot name="title">پاسخ نظر</x-slot>
            <x-slot name="body">
                <form action="{{ route('admin.post-comments.answer', $comment) }}" method="POST">
                    @csrf
                    <div class="row">

                        <div class="col-12">
                            <div class="form-group">
                                <label for="body"><strong>نظر: </strong>{{ $comment->body }}</label>
                                <textarea name="body" class="form-control" id="body" rows="5"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-success" type="submit">ثبت پاسخ</button>
                        <button class="btn btn-outline-danger" data-dismiss="modal">بستن</button>
                    </div>

                </form>
            </x-slot>
        </x-modal>
    @endforeach
@endsection
