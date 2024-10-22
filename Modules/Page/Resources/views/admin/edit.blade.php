@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست صفحات', 'route_link' => 'admin.pages.index'], ['title' => 'ویرایش صفحه']]" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ویرایش صفحه</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.pages.update', $page) }}" method="POST" class="save" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                placeholder="عنوان را وارد کنید" value="{{ old('title', $page->title) }}" required
                                autofocus />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="slug" class="control-label">اسلاگ:</label>
                            <input type="text" id="slug" class="form-control" name="slug"
                                placeholder="اسلاگ را وارد کنید" value="{{ old('slug', $page->slug) }}" autofocus />
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    @include('components.editor', [
                        'name' => 'text',
                        'required' => 'true',
                        'field_name' => 'text',
                        'model' => $page,
                    ])
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">ویرایش و ذخیره</button>
                            <a class="btn btn-outline-danger" href="{{ route('admin.pages.index') }}">برگشت</a>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
