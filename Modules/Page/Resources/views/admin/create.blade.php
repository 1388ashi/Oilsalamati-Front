@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست صفحات', 'route_link' => 'admin.pages.index'], ['title' => 'ثبت صفحه جدید']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت صفحه جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.pages.store') }}" method="POST">
                @csrf
                <div class="row mx-1">
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
                            <label for="slug" class="control-label">اسلاگ:</label>
                            <input type="text" id="slug" class="form-control" name="slug"
                                placeholder="اسلاگ را وارد کنید" value="{{ old('slug') }}" autofocus />
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    @include('components.editor', [
                        'name' => 'text',
                        'required' => 'true',
                        'field_name' => 'text',
                    ])
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-primary">ثبت و ذخیره</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
