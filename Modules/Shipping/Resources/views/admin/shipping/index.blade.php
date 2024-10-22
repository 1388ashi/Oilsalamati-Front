@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست حمل و نقل ها']]" />
        @can('write_shipping')
            <x-create-button route="admin.shippings.create" title="حمل و نقل جدید" />
        @endcan
    </div>
    <x-card>
        <x-slot name="cardTitle">لیست حمل و نقل ها ({{ $totalShipping }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'لوگو', 'مبلغ پیش فرض (تومان)', 'عمومی', 'بازه ', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($shippings as $shipping)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $shipping->name }}</td>
                            <td scope="row">
                                {{-- <a href="{{ $shipping->logo->url }}" target="_blank">
                                    <div class="bg-light" style="max-height: 70px; height: 457px;">
                                        <img src="{{ $shipping->logo->url }}">
                                    </div>
                                </a> --}}
                                -
                            </td>
                            <td>{{ number_format($shipping->default_price) }}</td>
                            <td>
                                @if ($shipping->isPublic())
                                    <span><i class="text-success fs-26 fa fa-check-circle-o"></i></span>
                                @else
                                    <span><i class="text-danger fs-24 fa fa-close"></i></span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.shipping-ranges.index', $shipping) }}"
                                    class="btn btn-sm btn-rss btn-icon text-white">
                                    <i class="fa fa-align-justify"></i>
                                </a>
                            </td>
                            <td>
                                @if ($shipping->status)
                                    <x-badge text="فعال" type="success" isLight="true" fontSize="14" />
                                @else
                                    <x-badge text="غیر فعال" type="danger" isLight="true" fontSize="14" />
                                @endif
                            </td>
                            <td>{{ verta($shipping->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @include('core::includes.show-icon-button', [
                                    'model' => $shipping,
                                    'route' => 'admin.shippings.show',
                                ])
                                @can('modify_shipping')
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $shipping,
                                        'route' => 'admin.shippings.edit',
                                    ])
                                @endcan
                                @can('delete_shipping')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $shipping,
                                        'route' => 'admin.shippings.destroy',
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $shippings->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
