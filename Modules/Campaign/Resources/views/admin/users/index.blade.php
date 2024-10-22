@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست کمپین ها', 'route_link' => 'admin.campaigns.index'], ['title' => 'لیست کاربران']])
        <x-breadcrumb :items="$items" />
    </div>

    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">لیست کاربران کمپین ({{ $campaign->title }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'شناسه', 'شماره تماس'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($users as $user)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->mobile }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 3])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $users->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
