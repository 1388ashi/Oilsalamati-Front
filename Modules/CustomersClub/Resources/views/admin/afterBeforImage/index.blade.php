@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست تصاویر قبل و بعد ها']]" />
        <x-create-button type="modal" target="createImageModal" title="ثبت تصویر قبل و بعد" />
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">لیست تصاویر قبل و بعد ({{ number_format(count($afterBeforImages)) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'مشتری', 'محصول', 'تصویر قبل', 'تصویر بعد', 'وضعیت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($afterBeforImages as $afterBeforImage)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $afterBeforImage['customer']['full_name'] }}</td>
                            <td>{{ $afterBeforImage['product']['title'] }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>@include('customersclub::admin.afterBeforImage.status', [
                                'approved' => $afterBeforImage['approved'],
                            ])</td>
                            {{-- <td class="text-center">
                        <a href="{{ $afterBeforImage['before_image'] }}" target="_blank">
                        <div class="bg-light pb-1 pt-1 img-holder img-show w-100" style="max-height: 60px; border-radius: 4px;">
                            <img src="{{ $afterBeforImage['before_image'] }}" style="height: 50px;" alt="{{ $afterBeforImage['before_image'] }}">
                        </div>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ $afterBeforImage['after_image'] }}" target="_blank">
                        <div class="bg-light pb-1 pt-1 img-holder img-show w-100" style="max-height: 60px; border-radius: 4px;">
                            <img src="{{ $afterBeforImage['after_image'] }}" style="height: 50px;" alt="{{$afterBeforImage['after_image']}}">
                        </div>
                        </a>
                    </td> --}}
                            <td>
                                {{-- Edit --}}
                                @include('core::includes.edit-modal-button', [
                                    'target' => '#edit-image-' . $afterBeforImage['id'],
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData"></x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
    @include('customersclub::admin.afterBeforImage.create')
    @include('customersclub::admin.afterBeforImage.edit')
@endsection
@section('scripts')
    <script>
        $('.search-customer-ajax').select2({
            ajax: {
                url: '{{ route('admin.customers.search') }}',
                dataType: 'json',
                processResults: (response) => {
                    let customers = response.data.customers || [];

                    return {
                        results: customers.map(customer => ({
                            id: customer.id,
                            mobile: customer.mobile,
                        })),
                    };
                },
                cache: true,
            },
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

                let text = `شناسه: ${repo.id} | موبایل: ${repo.mobile}`;
                $container.find(".select2-result-repository__title").text(text);
                return $container;
            },
            minimumInputLength: 1,
            templateSelection: (repo) => {
                return repo.mobile ? `موبایل: ${repo.mobile}` : repo.text;
            }
        });
    </script>
@endsection
