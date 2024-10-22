@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست پرسش محصولات', 'route_link' => 'admin.product-questions.index'],
                ['title' => 'پاسخ'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle"><span class="font-weight-normal"> پاسخ پرسش : </span>{{ $question->body }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>نظر</th>
                        <th>محصول</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($questions as $childQuestion)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td style="white-space: wrap">{{ $childQuestion->body }}</td>
                            <td>{{ $childQuestion->product->title }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot
                                        name="type">{{ config('productcomment.status_color.' . $childQuestion->status) }}</x-slot>
                                    <x-slot
                                        name="text">{{ config('productcomment.statuses.' . $childQuestion->status) }}</x-slot>
                                </x-badge>
                            </td>
                            <td>{{ verta($childQuestion->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @include('core::includes.delete-icon-button', [
                                    'model' => $childQuestion,
                                    'route' => 'admin.product-questions.destroy',
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 8])
                    @endforelse
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
