@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست مطالب']]" />
        @can('write_post')
            <x-create-button route="admin.posts.create" title="مطلب جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.posts.index') }}" method="GET" class="col-12">
                <div class="row">

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>عنوان :</label>
                            <input type="text" class="form-control" name="title" value="{{ request('title') }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>انتخاب دسته بندی :</label>
                            <select class="form-control" name="category_id" id="CategorySelect">
                                <option value="">انتخاب</option>
                                <option value="all" {{ request('category_id') == 'all' ? 'selected' : null }}>همه
                                </option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category_id') == $category->id ? 'selected' : null }}>
                                        {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>شناسه :</label>
                            <input type="number" class="form-control" name="id" value="{{ request('id') }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>انتخاب وضعیت :</label>
                            <select class="form-control" name="status" id="StatusSelect">
                                <option value="">انتخاب</option>
                                <option value="all" {{ request('status') == 'all' ? 'selected' : null }}>همه</option>
                                @foreach (config('blog.statuses') as $name => $label)
                                    <option value="{{ $name }}"
                                        {{ request('status') == $name ? 'selected' : null }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="start_date_show">از تاریخ :</label>
                            <input class="form-control fc-datepicker" id="start_date_show" type="text"
                                autocomplete="off" />
                            <input name="start_date" id="start_date_hide" type="hidden"
                                value="{{ request('start_date') }}" />
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="end_date_show">تا تاریخ :</label>
                            <input class="form-control fc-datepicker" id="end_date_show" type="text"
                                autocomplete="off" />
                            <input name="end_date" id="end_date_hide" type="hidden" value="{{ request('end_date') }}" />
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-xl-9 col-lg-8 col-md-6 col-12">
                        <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر ها <i
                                class="fa fa-close"></i></a>
                    </div>

                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">مطالب ({{ number_format($posts->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'عنوان', 'دسته بندی', 'تعداد بازدید', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($posts as $post)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $post->title }}</td>
                            {{--                  <td class="text-center m-0 p-0"> --}}
                            {{--                    @if ($post->image) --}}
                            {{--                      <figure class="figure my-2"> --}}
                            {{--                        <a target="_blank" href="{{ Storage::url($post->image['uuid'] .'/'. $post->image['file_name']) }}"> --}}
                            {{--                          <img --}}
                            {{--                            src="{{ Storage::url($post->image['uuid'] .'/'. $post->image['file_name']) }}" --}}
                            {{--                            class="img-thumbnail" --}}
                            {{--                            alt="image" --}}
                            {{--                            width="50" --}}
                            {{--                            style="max-height: 32px;" --}}
                            {{--                          /> --}}
                            {{--                        </a> --}}
                            {{--                      </figure> --}}
                            {{--                    @else --}}
                            {{--                      <span> - </span> --}}
                            {{--                    @endif --}}
                            {{--                  </td> --}}
                            <td class="text-center">{{ $post->category->name }}</td>
                            <td class="text-center">{{ $post->views_count }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $post->getStatusColorAttribute() }}">
                                    {{ config('blog.statuses.' . $post->status) }}
                                </span>
                            </td>
                            <td class="text-center">{{ verta($post->created_at)->format('Y/m/d H:i') }}</td>
                            <td class="text-center">

                                @can('read_comment')
                                    <a href="{{ route('admin.post-comments.index', $post) }}"
                                        class="btn btn-sm btn-icon btn-green text-white position-relative" style=""
                                        data-toggle="tooltip" data-original-title="نظرات">
                                        <i class="fa fa-comment"></i>
                                        <span
                                            class="font-weight-bold text-white fs-8 d-flex align-items-center justify-content-center position-absolute bg-black-9"
                                            style="
                            top: -11px;
                            right: -8px;
                            width: 18px;
                            height: 18px;
                            border-radius: 50px;">
                                            {{ $post->comments->count() }}
                                        </span>
                                    </a>

                                    {{-- <a
                        href="{{ route('admin.post-comments.index', $post) }}"
                        class="btn btn-sm btn-icon btn-green text-white"
                        style=""
                        data-toggle="tooltip"
                        data-original-title="نظرات">
                        <i class="fa fa-comment"></i>
                      </a> --}}
                                @endcan

                                @include('core::includes.show-icon-button', [
                                    'model' => $post,
                                    'route' => 'admin.posts.show',
                                ])

                                @can('modify_post')
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $post,
                                        'route' => 'admin.posts.edit',
                                    ])
                                @endcan

                                @can('delete_post')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $post,
                                        'route' => 'admin.posts.destroy',
                                        'disabled' => !$post->isDeletable(),
                                    ])
                                @endcan

                            </td>
                        </tr>
                    @empty

                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $posts->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])

    <script>
        $('#CategorySelect').select2({
            placeholder: 'انتخاب دسته بندی'
        });
        $('#StatusSelect').select2({
            placeholder: 'انتخاب وضعیت'
        });
    </script>
@endsection
