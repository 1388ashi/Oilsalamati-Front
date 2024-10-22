@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست خبرنامه ها']]" />
        @can('write_post')
            <x-create-button route="admin.newsletters.create" title="خبرنامه جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست همه خبرنامه ها ({{ number_format($newsletters->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>عنوان</th>
                        <th>وضعیت</th>
                        <th>تاریخ ارسال</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($newsletters as $newsletter)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $newsletter->title }}</td>
                            <td>@include('newsletters::admin.status', ['status' => $newsletter->status])</td>
                            <td>{{ verta($newsletter->send_at) }}</td>
                            <td>{{ verta($newsletter->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @include('core::includes.delete-icon-button', [
                                    'model' => $newsletter,
                                    'route' => 'admin.newsletters.destroy',
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 6])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $newsletters->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
