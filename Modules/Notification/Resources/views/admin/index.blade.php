@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست پیام ها']])
        <x-breadcrumb :items="$items" />
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">ثبت پیام</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.notifications_public.add') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 form-group">
                        <label>پیام:</label>
                        <input type="text" name="text" placeholder="پیام خود را بنویسید" class="form-control" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <button class="btn btn-primary align-self-center">ارسال پیام</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
    <x-card>
        <x-slot name="cardTitle"> پیام ها ({{ number_format($notifications_public->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'متن پیام', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($notifications_public as $notification)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $notification->text }}</td>
                            <td>
                                @include('core::includes.delete-icon-button', [
                                    'model' => $notification,
                                    'route' => 'admin.notifications_public.delete',
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 3])
                    @endforelse
                </x-slot>
                <x-slot
                    name="extraData">{{ $notifications_public->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
    <!-- row closed -->
@endsection
