@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست هدایا']]" />
        <x-create-button route="admin.order-gift-ranges.create" title="هدیه جدید" />
    </div>

    <x-card>
        <x-slot name="cardTitle">هدایا ({{ $gifts->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'عنوان', 'تصویر', 'قیمت (تومان)', 'حداقل سفارش (تومان)', 'توضیحات', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($gifts as $gift)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $gift->title }}</td>
                            <td class="m-0 p-0">
                                @if ($gift->image)
                                    <figure class="figure my-2">
                                        <a target="_blank" href="{{ $gift->getFirstMediaUrl('images') }}">
                                            <img src="{{ asset('storage/' . $gift->getMedia('images')[0]->uuid . '/' . $gift->getMedia('images')[0]->file_name) }}"
                                                class="img-thumbnail" alt="image" width="70"
                                                style="max-height: 40px;" />
                                        </a>
                                    </figure>
                                @else
                                    <span> - </span>
                                @endif
                            </td>
                            <td>{{ number_format($gift->price) }}</td>
                            <td>{{ number_format($gift->min_order_amount) }}</td>
                            <td style="white-space: wrap;">{{ $gift->description ?? '-' }}</td>
                            <td>{{ verta($gift->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @include('core::includes.edit-icon-button', [
                                    'model' => $gift,
                                    'route' => 'admin.order-gift-ranges.edit',
                                ])
                                @include('core::includes.delete-icon-button', [
                                    'model' => $gift,
                                    'route' => 'admin.order-gift-ranges.destroy',
                                    'disabled' => !$gift->isDeletable(),
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 8])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $gifts->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
