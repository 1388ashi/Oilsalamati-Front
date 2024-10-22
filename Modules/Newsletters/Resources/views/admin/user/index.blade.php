@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست اعضا خبرنامه']]" />
    </div>


    <x-card>
        <x-slot name="cardTitle">تمام اعضای خبرنامه ({{ number_format($userNewsletters->total()) }})</x-slot>
        <x-slot name="cardOptions">
            <div class="card-options">
                <button class="btn btn-outline-info btn-sm" id="download-btn">دانلود اکسل</button>
            </div>
        </x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه</th>
                        <th>شماره تماس</th>
                        <th>ایمیل</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($userNewsletters as $userNewsletter)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $userNewsletter->id }}</td>
                            <td>{{ $userNewsletter->phone_number ?? '-' }}</td>
                            <td>{{ $userNewsletter->email }}</td>
                            <td>{{ verta($userNewsletter->created_at) }}</td>
                            <td>
                                @include('core::includes.delete-icon-button', [
                                    'model' => $userNewsletter,
                                    'route' => 'admin.newsletters.users.destroy',
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 8])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $userNewsletters->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
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
