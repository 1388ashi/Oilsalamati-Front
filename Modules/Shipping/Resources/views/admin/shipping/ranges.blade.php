@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست حمل و نقل ها', 'route_link' => 'admin.shippings.index'],
                ['title' => 'نمایش حمل و نقل', 'route_link' => 'admin.shippings.show', 'parameter' => $shipping],
                ['title' => 'بازه ها'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
        @can('write_shipping')
            <x-create-button type="modal" target="create-range-modal" title="بازه جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست بازه ها</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'شناسه', 'پایین (گرم)', 'بالا (گرم)', 'مبلغ (تومان)', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($shippingRanges as $range)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $range->id }}</td>
                            <td>{{ number_format($range->lower) }}</td>
                            <td>{{ number_format($range->higher) }}</td>
                            <td>{{ number_format($range->amount) }}</td>
                            <td>{{ verta($range->created_at)->format('Y/m/d H:i') }}</td>
                            <td>

                                @can('modify_shipping')
                                    @include('core::includes.edit-modal-button', [
                                        'target' => '#edit-range-modal-' . $range->id,
                                    ])
                                @endcan

                                @can('delete_shipping')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $range,
                                        'route' => 'admin.shipping-ranges.destroy',
                                    ])
                                @endcan

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

    @include('shipping::admin.shipping.includes.create-range-modal')
    @include('shipping::admin.shipping.includes.edit-range-modal')
@endsection
