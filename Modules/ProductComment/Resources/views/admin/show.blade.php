@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item"><a href="{{ route('admin.post-comments.all') }}">لیست نظرات مطالب</a></li>
            <li class="breadcrumb-item active">پاسخ های نظر</li>
        </ol>
    </div>
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title"> پاسخ نظر : <strong>{{ $comment->body }}</strong></p>
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
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comment->children as $comment)
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

                                    @include('core::includes.data-not-found-alert', ['colspan' => 7])
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCommentModal-{{ $comment->id }}" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <p class="modal-title" style="font-size: 20px;">ویرایش نظر کد - {{ $comment->id }}</p>
                    <button aria-label="Close" class="close" data-dismiss="modal"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
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
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showCommentAnswerModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <p class="modal-title" style="font-size: 20px;">پاسخ نظر</p>
                    <button aria-label="Close" class="close" data-dismiss="modal"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
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
                </div>
            </div>
        </div>
    </div>
@endsection
