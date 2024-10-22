@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'گزارش بازدید های سایت']]" />
    </div>

    <x-card>
        <x-slot name="cardTitle">بازدید های سایت</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>تاریخ</th>
                        <th>تعداد بازدید</th>
                        <th>مشاهده جزئیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($siteviews as $siteview)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ verta($siteview->date)->format('Y/m/d') }}</td>
                            <td>{{ $siteview->total_count }}</td>
                            <td>
                                <button onclick='showDetail(@json($siteview->date), @json(verta($siteview->date)->format('Y/m/d')))'
                                    class="btn btn-sm btn-primary btn-icon text-white">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 6])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $siteviews->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    <x-modal id="show-siteview-detail-modal" size="md">
        <x-slot name="title"><span id="title"></span></x-slot>
        <x-slot name="body">
            <table class="table table-striped table-bordered text-nowrap text-center">
                <thead class="border-top">
                    <th>ردیف</th>
                    <th>ساعت</th>
                    <th>تعداد نفرات</th>
                </thead>
                <tbody>
                </tbody>
            </table>
        </x-slot>
    </x-modal>
@endsection

@section('scripts')
    <script>
        const modal = $('#show-siteview-detail-modal');
        const modalTitle = modal.find('#title');

        const table = modal.find('table');
        const tableBody = table.find('tbody');

        function showDetail(date, jalaliDate) {

            tableBody.empty();
            modalTitle.text('مشاهده آمار بازدید های سایت در تاریخ' + ' ' + jalaliDate);
            modal.modal('show');

            $.ajax({
                url: '{{ route('admin.reports.load-siteviews') }}',
                type: 'GET',
                data: {
                    date: date
                },
                success: function(response) {
                    console.log(response);

                    let counter = 1;

                    response.data.siteviews.forEach(siteview => {

                        let row = '';

                        row += '<tr>';
                        row += `<td class="font-weight-bold">${counter}</td>`;
                        row += `<td>${siteview.hour}</td>`;
                        row += `<td>${siteview.count}</td>`;
                        row += '</tr>';

                        tableBody.append(row);
                        counter++;

                    });
                }
            });
        }
    </script>
@endsection
