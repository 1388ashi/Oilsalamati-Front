@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست مشتریان معتبر', 'route_link' => 'admin.valid-customers.index'], ['title' => 'ویرایش مشتری معتبر جدید']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ویرایش مشتری معتبر کد - {{ $customer->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.valid-customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">

                    <div class="col-12 col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="name" class="control-label"> نام: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="name" class="form-control" name="name"
                                value="{{ old('name', $customer->name) }}" required autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="link" class="control-label"> لینک: </label>
                            <input type="text" id="link" class="form-control" name="link"
                                value="{{ old('link', $customer->link) }}" />
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label"> تصویر: </label>
                            <input type="file" id="image" class="form-control" name="image"
                                value="{{ old('image') }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="control-label">توضیحات </label>
                            <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $customer->description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="label" class="control-label"> وضعیت: </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', $customer->status) == 1 ? 'checked' : null }} />
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
