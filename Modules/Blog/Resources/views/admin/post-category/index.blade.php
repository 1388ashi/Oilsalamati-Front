@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست دسته بندی های مطالب']]" />
        @can('write_post-category')
            <x-create-button type="modal" target="createPostCategoryModal" title="دسته بندی جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.post-categories.index') }}" method="GET" class="col-12">
                <div class="row">

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>عنوان :</label>
                            <input type="text" class="form-control" name="name" value="{{ request('name') }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>انتخاب وضعیت :</label>
                            <select class="form-control" name="status">
                                <option value="">همه</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : null }}>فعال</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : null }}>غیر فعال</option>
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
                        <a href="{{ route('admin.post-categories.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر
                            ها <i class="fa fa-close"></i></a>
                    </div>

                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">دسته بندی های مطالب ({{ number_format($postCategories->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'تعداد مطالب', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($postCategories as $postCategory)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $postCategory->name }}</td>
                            <td class="text-center">{{ $postCategory->countPosts() }}</td>
                            <td class="text-center">@include('core::includes.status', ['status' => $postCategory->status])</td>
                            <td class="text-center">{{ verta($postCategory->created_at)->format('Y/m/d H:i:s') }}</td>
                            <td class="text-center">

                                @can('modify_post-category')
                                    @include('core::includes.edit-modal-button', [
                                        'target' => '#editPostCategoryModal-' . $postCategory->id,
                                    ])
                                @endcan

                                @can('delete_post-category')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $postCategory,
                                        'route' => 'admin.post-categories.destroy',
                                        'disabled' => !$postCategory->isDeletable(),
                                    ])
                                @endcan

                            </td>
                        </tr>
                    @empty

                        @include('core::includes.data-not-found-alert', ['colspan' => 8])
                    @endforelse
                </x-slot>
                <x-slot
                    name="extraData">{{ $postCategories->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @can('write_post-category')
        @include('blog::admin.post-category.includes.create-modal')
    @endcan

    @can('modify_post-category')
        @include('blog::admin.post-category.includes.edit-modal')
    @endcan
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
@endsection
