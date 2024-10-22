@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست مشتریان معتبر']]" />
        <x-create-button route="admin.valid-customers.create" title="مشتری معتبر جدید" />
    </div>
    <x-card>
        <x-slot name="cardTitle">مشتریان معتبر ({{ number_format($customers->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'تصویر', 'توضیحات', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $customer->name }}</td>
                            <td class="m-0 p-0">
                            {{-- @if ($customer->image)
                                    <figure class="figure my-2">
                                    <a class="avatar avatar-xxl brround" target="_blank" href="{{ $customer->image->url }}">
                                        <img src="{{ $customer->image->url }}"/>
                                    </a>
                                    </figure>
                                @else --}}
                                <span> - </span>
                                {{-- @endif --}}
                            </td>
                            <td style="white-space: wrap">{{ $customer->description }}</td>
                            <td>@include('core::includes.status', ['status' => $customer->status])</td>
                            <td>{{ verta($customer->created_at)->format('Y/m/d H:i') }}</td>
                            <td>

                                @include('core::includes.edit-icon-button', [
                                    'model' => $customer,
                                    'route' => 'admin.valid-customers.edit',
                                ])

                                @include('core::includes.delete-icon-button', [
                                    'model' => $customer,
                                    'route' => 'admin.valid-customers.destroy',
                                ])

                            </td>
                        </tr>
                    @empty

                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $customers->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
