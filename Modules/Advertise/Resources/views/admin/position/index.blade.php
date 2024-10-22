@extends('admin.layouts.master')

@section('content')
<div class="page-header">
    <x-breadcrumb :items="[['title' => 'لیست همه جایگاه ها']]" />
    <x-create-button type="modal" target="createPositionModal" title="جایگاه جدید" />
</div>

<x-card>
    <x-slot name="cardTitle">لیست همه جایگاه ها ({{ number_format($positions->total()) }})</x-slot>
    <x-slot name="cardOptions"><x-card-options /></x-slot>
    <x-slot name="cardBody">
        @include('components.errors')
        <x-table-component>
            <x-slot name="tableTh">
                <tr>
                    @php($tableTh = ['ردیف', 'نام','کلید','وضعیت', 'تاریخ ثبت','بنر ها', 'عملیات'])
                    @foreach ($tableTh as $th)
                        <th>{{ $th }}</th>
                    @endforeach
                </tr>
            </x-slot>
            <x-slot name="tableTd">
                @forelse($positions as $position)
                <tr>
                    <td class="font-weight-bold">{{$loop->iteration}}</td>
                    <td>{{ $position->label }}</td>
                    <td>{{ $position->key }}</td>
                    <td>@include('core::includes.status',["status" => $position->status])</td>
                    <td>{{verta($position->created_at)->format('Y/m/d H:i')}}</td>
                    <td><a class="action-btns1 pt-1 px-2" href="{{route('admin.advertisements.edit_possibility',$position)}}"><i class="fe fe-menu text-info"></i></a></td>
                    <td>
                        {{-- Edit--}}
                        @include('core::includes.edit-modal-button',[
                            'target' => "#edit-position-" . $position->id
                        ])
                    </td>
                </tr>
                @empty
                    @include('core::includes.data-not-found-alert', ['colspan' => 7])

                @endforelse
            </x-slot>
            <x-slot name="extraData">{{ $positions->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
        </x-table-component>
    </x-slot>
</x-card>
    @include('advertise::admin.position.edit')
    @include('advertise::admin.position.create')
    <!-- row closed -->

@endsection
