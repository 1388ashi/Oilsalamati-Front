@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست مشتریان معتبر', 'route_link' => 'admin.valid-customers.index'], ['title' => 'ایجاد مشتری معتبر جدید']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت مشتری جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.valid-customers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">

                    <div class="col-12 col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="name" class="control-label"> نام: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="name" class="form-control" name="name"
                                placeholder="نام را وارد کنید" value="{{ old('name') }}" required autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="link" class="control-label"> لینک: </label>
                            <input type="text" id="link" class="form-control" name="link"
                                placeholder="لینک را وارد کنید" value="{{ old('link') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label"> تصویر: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="file" id="image" class="form-control" name="image"
                                value="{{ old('image') }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="control-label">توضیحات </label>
                            <textarea class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="label" class="control-label"> وضعیت: </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', 1) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">فعال</span>
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
                            title: product.title,
                        })),
                    };
                },
                cache: true,
            },
            placeholder: 'عنوان محصول را وارد کنید',
            templateResult: (repo) => {
                if (repo.loading) {
                    return "در حال بارگذاری...";
                }

                let $container = $(
                    "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'></div>" +
                    "</div>" +
                    "</div>"
                );

                let text = repo.title;
                $container.find(".select2-result-repository__title").text(text);

                return $container;
            },
            minimumInputLength: 1,
            templateSelection: (repo) => {
                return repo.id ? repo.title : repo.text;
            }
        });
    </script>
@endsection
