@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مدیریت درخواست تبدیل بن']])
        <x-breadcrumb :items="$items" />
    </div>

    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">مدیریت درخواست تبدیل بن ({{ number_format($list->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'مشتری', 'تعداد بن(تومان)', 'ارزش تبدیل شده', 'وضعیت', 'تاریخ تایید', 'تاریخ ثبت', 'توضیحات', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($list as $item)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td style="white-space: wrap">
                                {{ $item->customer->full_name . '(' . $item->customer->mobile . ')' }}
                            </td>
                            <td style="white-space: wrap">{{ $item->requested_bon }}</td>
                            <td style="white-space: wrap">{{ number_format($item->converted_gift_value) }}</td>
                            <td>@include('customersclub::admin.exchangeBon.status', [
                                'status' => $item->status,
                            ])</td>
                            <td style="white-space: wrap">{{ verta($item->created_at)->format('Y/m/d H:i') }}</td>
                            <td style="white-space: wrap">{{ verta($item->action_date)->format('Y/m/d H:i') }}</td>
                            <td style="white-space: wrap">{{ $item->description ? $item->description : '-' }}</td>
                            <td>
                                @if ($item->status == 'new')
                                    <button class="btn btn-sm btn-icon btn-warning text-white"
                                        data-target= "#edit-exchangeBon-{{ $item->id }}" data-toggle="modal"
                                        type="button" data-original-title="ویرایش">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                @else
                                    <div class="position-relative">
                                        <span class="font-weight-bold"
                                        style="color: white;font-size: 13px;content:\2713;position: absolute;top: -11px;right: -2px;width: 18px;height: 18px;background: blue;border-radius: 50px;display: flex;align-items: center;justify-content: center;">&#10003;</span>
                                        <button style="background-color: rgba(0, 128, 0, .799);"
                                            class="btn btn-icon btn-sm text-white"
                                            data-target= "#edit-exchangeBon-{{ $item->id }}" data-toggle="modal"
                                            type="button" data-original-title="ویرایش">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $list->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
    @include('customersclub::admin.exchangeBon.edit')
@endsection
