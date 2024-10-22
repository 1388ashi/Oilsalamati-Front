@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست دسته بندی محصولات', 'route_link' => 'admin.categories.index'], ['title' => 'ویرایش دسته بندی']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ویرایش دسته بندی</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                placeholder="عنوان را وارد کنید" value="{{ old('title', $category->title) }}" required
                                autofocus />
                            <span class="text-muted-dark mt-2 mr-1 font-weight-bold fs-11">عنوان را حتما به <span
                                    class="text-danger">فارسی</span> وارد کنید!</span>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="en_title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="en_title" class="form-control" name="en_title"
                                placeholder="عنوان را وارد کنید" value="{{ old('en_title', $category->en_title) }}" required
                                autofocus />
                            <span class="text-muted-dark mt-2 mr-1 font-weight-bold fs-11">عنوان را حتما به <span
                                    class="text-danger">انگیلیسی</span> وارد کنید!</span>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="parent_id" class="control-label"> پدر:</label>
                            <select class="form-control" name="parent_id" id="parent_id">
                                <option value="" class="text-muted">ندارد</option>
                                @php($oldValue = old('parent_id', $category->parent_id))
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $oldValue == $category->id ? 'selected' : '' }}>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="attribute_ids" class="control-label"> ویژگی:</label>
                            <select class="form-control select2" multiple name="attribute_ids[]" id="attribute_ids">
                                <option value="" class="text-muted">ندارد</option>
                                @foreach ($attributes as $attribute)
                                    @php($isSelected = $category->attributes->contains($attribute->id) || in_array($attribute->id, old('attribute_ids', [])))
                                    <option value="{{ $attribute->id }}" {{ $isSelected ? 'selected' : null }}>
                                        {{ $attribute->label }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="specification_ids" class="control-label"> مشخصات:</label>
                            <select class="form-control select2" multiple name="specification_ids[]" id="specification_ids">
                                <option value="" class="text-muted">ندارد</option>
                                @foreach ($specifications as $specification)
                                    @php($isSelected = $category->specifications->contains($specification->id) || in_array($specification->id, old('specification_ids', []))))
                                    <option value="{{ $specification->id }}" {{ $isSelected ? 'selected' : null }}>
                                        {{ $specification->label }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label"> تصویر: </label>
                            <input type="file" id="image" class="form-control" name="image"
                                value="{{ old('image') }}">
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="icon" class="control-label"> آیکون: </label>
                            <input type="file" id="icon" class="form-control" name="icon"
                                value="{{ old('icon') }}">
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="banner_link" class="control-label"> لینک بنر: </label>
                            <input type="text" id="banner_link" class="form-control" name="banner_link"
                                value="{{ old('banner_link', $category->banner_link) }}">
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="description" class="control-label">توضیحات:</label>
                            <textarea class="form-control" name="description" id="description" rows="2">{{ old('description', $category->description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="meta_description" class="control-label">توضیحات متا:</label>
                            <textarea class="form-control" name="meta_description" id="meta_description" rows="2">{{ old('meta_description', $category->meta_description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="meta_title" class="control-label"> متا: </label>
                            <input type="text" id="meta_title" class="form-control" name="meta_title"
                                value="{{ old('meta_title', $category->meta_title) }}">
                        </div>
                    </div>


                    <div class="col-12">
                        <div class="form-group">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', $category->status) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">وضعیت</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="special" value="1"
                                    {{ old('special', $category->special) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">ویژه</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="show_in_home" value="1"
                                    {{ old('show_in_home', $category->show_in_home) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">نمایش محصولات در صفحه اصلی</span>
                            </label>
                        </div>
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
    <script>
        $('#attribute_ids').select2({
            placeholder: 'انتخاب ویژگی',
        });
        $('#specification_ids').select2({
            placeholder: 'انتخاب مشخصه',
        });
    </script>
@endsection
