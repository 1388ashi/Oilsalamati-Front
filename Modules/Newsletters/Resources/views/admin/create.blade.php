@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[
            ['title' => 'لیست خبرنامه ها', 'route_link' => 'admin.newsletters.index'],
            ['title' => 'ثبت خبرنامه جدید'],
        ]" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت خبرنامه جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.newsletters.store') }}" method="POST" class="save" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                placeholder="عنوان را وارد کنید" value="{{ old('title') }}" required autofocus />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="from_published_at_show" class="control-label">تاریخ انتشار :
                                <span class="text-danger">&starf;</span>
                            </label>
                            <input class="form-control fc-datepicker" id="from_published_at_show" type="text"
                                autocomplete="off" placeholder="تاریخ انتشار را انتخاب کنید" />
                            <input name="send_at" id="from_published_at_hide" type="hidden" value="{{ old('send_at') }}" />
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="body" class="control-label">خبر :
                            <span class="text-danger">&starf;</span>
                        </label>
                        @include('components.editor', [
                            'name' => 'body',
                            'required' => 'true',
                            'editor_id' => 'body',
                        ])
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-primary" type="submit">ثبت و ذخیره</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'from_published_at_hide',
        'textInputId' => 'from_published_at_show',
    ])
@endsection
