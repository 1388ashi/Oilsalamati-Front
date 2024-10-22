@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'مدیریت ارزش بن']]" />
        <x-create-button type="modal" target="createBonModal" title="ثبت بن جدید" />
    </div>

    <x-card>
        <x-slot name="cardTitle">مدیریت ارزش بن</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'مقدار', 'تاریخ ثبت'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($list as $item)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ number_format($item->value) }}</td>
                            <td>{{ verta($item->date)->format('Y/m/d') }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 3])
                    @endforelse
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    <x-modal id="createBonModal" size="md">
        <x-slot name="title">افزودن امتیاز خرید جدید</x-slot>
        <x-slot name="body">
            <form action="{{ route('admin.customersClub.addBonValue') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">ارزش بن :<span class="text-danger">&starf;</span></label>
                                <input type="text" id="value" class="form-control comma" name="value"
                                    placeholder="عنوان را وارد کنید" value="{{ old('value') }}" required autofocus />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-primary  text-right item-right">ثبت</button>
                    <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
                </div>
            </form>
        </x-slot>
    </x-modal>
@endsection
