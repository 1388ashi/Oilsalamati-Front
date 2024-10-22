@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست مطالب', 'route_link' => 'admin.posts.index'], ['title' => 'ویرایش مطلب']])
        <x-breadcrumb :items="$items" />
    </div>


    <x-card>
        <x-slot name="cardTitle">ویرایش مطلب</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.posts.update', $post) }}" method="POST" class="save" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row">

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                value="{{ old('title', $post->title) }}" required autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="post_category_id" class="control-label"> دسته بندی: <span
                                    class="text-danger">&starf;</span></label>
                            <select class="form-control" name="post_category_id" id="post_category_id">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('post_category_id', $post->post_category_id) == $category->id ? 'selected' : null }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="from_published_at_show" class="control-label">تاریخ انتشار :</label>
                            <input class="form-control fc-datepicker" id="from_published_at_show" type="text"
                                autocomplete="off" placeholder="تاریخ انتشار را انتخاب کنید" />
                            <input name="published_at" id="from_published_at_hide" type="hidden"
                                value="{{ old('published_at', $post->published_at) }}" />
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="read_time" class="control-label"> زمان مطالعه: </label>
                            <input type="number" id="read_time" class="form-control" name="read_time"
                                value="{{ old('read_time', $post->read_time) }}">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="status" class="control-label"> وضعیت: <span
                                    class="text-danger">&starf;</span></label>
                            <select class="form-control" name="status" id="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', $post->status) == $status ? 'selected' : null }}>
                                        {{ config('blog.statuses.' . $status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label"> تصویر: </label>
                            <input type="file" id="image" class="form-control" name="image"
                                value="{{ old('image') }}">
                        </div>
                    </div>
                    @if ($imageSrc = optional($post->getMedia('image')->first())->getUrl())
                        <div class="col-12 text-center">
                            <div class="img-holder my-4 img-show w-100 bg-light" style="max-height: 300px;">
                                <img src="{{ $imageSrc }}" style="max-height: 300px">
                            </div>
                        </div>
                    @endif

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="summary" class="control-label">خلاصه:</label>
                            <textarea class="form-control" name="summary" id="summary" rows="2">{{ old('summary', $post->summary) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="meta_description" class="control-label">توضیحات متا:</label>
                            <textarea class="form-control" name="meta_description" id="meta_description" rows="2">{{ old('meta_description', $post->meta_description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="body" class="control-label">متن:<span class="text-danger">&starf;</span></label>
                            <textarea name="body" class="form-control">{{ old('body', $post->body) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="control-label"> انتخاب محصول: </label>
                            <select name="product_ids[]" multiple class="form-control search-product-ajax">
                                @foreach ($postProducts as $product)
                                    <option value="{{ $product->id }}" selected>{{ $product->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="label" class="control-label"> ویژه: </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="special" value="1"
                                    {{ old('special', $post->special) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">فعال</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">بروزرسانی</button>
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

    <script>
        $('.search-product-ajax').select2({
            ajax: {
                url: '{{ route('admin.products.search') }}',
                dataType: 'json',
                processResults: (response) => {
                    let products = response.data.products || [];

                    return {
                        results: products.map(product => ({
                            id: product.id,
                            text: product.title,
                        })),
                    };
                },
                cache: true,
            },
            templateResult: (repo) => {

                if (repo.loading) {
                    return "در حال بارگذاری...";
                }

                return repo.text || repo.title;
            },
            minimumInputLength: 1,
            templateSelection: (repo) => {
                return repo.text;
            }
        });
    </script>
@endsection
