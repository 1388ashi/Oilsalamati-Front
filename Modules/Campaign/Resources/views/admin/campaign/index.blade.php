@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست کمپین ها']]" />
        <div>
            <x-create-button route="admin.campaigns.create" title="ثبت کمپین جدید" />
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست همه کمپین ها ({{ $campaigns->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <div class="table-responsive">
                <table id="data-table" class="table table-striped table-bordered text-nowrap text-center">
                    <thead>
                        <tr>
                            <th class="border-top">ردیف</th>
                            <th class="border-top">عنوان</th>
                            <th class="border-top">شروع</th>
                            <th class="border-top">پایان</th>
                            <th class="border-top">وضعیت</th>
                            <th class="border-top">کاربران</th>
                            <th class="border-top">سوالات</th>
                            <th class="border-top">عملیات</th>
                            <th class="border-top">اکسل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                <td>{{ $campaign->title }}</td>
                                <td>{{ verta($campaign->start_date)->format('Y/m/d H:i') }}</td>
                                <td>{{ verta($campaign->end_date)->format('Y/m/d H:i') }}</td>
                                <td>@include('core::includes.status', ['status' => $campaign->status])</td>
                                <td><a href="{{ route('admin.campaignUsers', $campaign) }}" class="action-btns1  pt-1 px-2">
                                        <i class="feather feather-users text-primary"></i></a></td>
                                <td><a href="{{ route('admin.campaignQuestions.index', $campaign) }}"
                                        class="action-btns1  pt-1 px-2">
                                        <i class="fa fa-question  text-primary"></i></a></td>
                                <td>
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $campaign,
                                        'route' => 'admin.campaigns.edit',
                                    ])
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $campaign,
                                        'route' => 'admin.campaigns.destroy',
                                    ])
                                </td>
                                <td id="data-table">
                                    <form action="{{ route('admin.campaign.exel', $campaign) }}" method="GET">
                                        <span style="display: flex;justify-content: center;align-items: center;">
                                            <button class="action-btns1 pt-1 px-2"
                                                style="display: flex;justify-content: center;align-items: center;">
                                                <i class="feather feather-download text-success"></i>
                                            </button>
                                        </span>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <p class="text-danger"><strong>در حال حاضر هیچ کمپینی یافت نشد!</strong></p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    <script>
        document.getElementById('download-btn').addEventListener('click', function() {
            const table = document.getElementById('data-table');
            const workbook = XLSX.utils.table_to_book(table, {
                sheet: "Sheet1"
            });
            XLSX.writeFile(workbook, 'data.xlsx');
        });
    </script>
@endsection
