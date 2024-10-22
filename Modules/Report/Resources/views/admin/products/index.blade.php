@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'گزارش محصولات']]" />
    </div>


    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.reports.customers') }}" method="GET">
                <div class="row">
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="id" placeholder="شناسه محصول" value="{{ request('id') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="title" placeholder="عنوان محصول" value="{{ request('title') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                            placeholder="از تاریخ" />
                        <input name="start_date" id="start_date_hide" type="hidden" value="{{ old('start_date') }}" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                            placeholder="تا تاریخ" />
                        <input name="end_date" id="end_date_hide" type="hidden" value="{{ old('end_date') }}" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-9">
                        <button class="btn btn-primary btn-block">جستجو</button>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('admin.reports.customers') }}" class="btn btn-danger btn-block">حذف فیلتر ها
                            <i class="fa fa-close" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">لیست محصولات ({{ number_format($products->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه محصول</th>
                        <th>عنوان</th>
                        <th>تعداد فروش</th>
                        <th>میزان فروش (تومان)</th>
                        <th>تنوع ها</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($products as $product)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->title }}</td>
                            <td>{{ number_format($product->sales_count) }}</td>
                            <td>{{ number_format($product->total_sale_amount) }}</td>
                            <td>
                                <button onclick="showModal(@json($product->id))"
                                    class="btn btn-sm btn-outline-info">
                                    مشاهده
                                </button>
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 5])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $products->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    <x-modal id="show-product-varieties-modal" size="lg">
        <x-slot name="title"><span id="title"></span></x-slot>
        <x-slot name="body">
            <table class="table table-striped table-bordered text-nowrap text-center">
                <thead class="border-top">
                    <th>ردیف</th>
                    <th>تنوع</th>
                    <th>تعدا فروش</th>
                    <th>میزان فروش (تومان)</th>
                </thead>
                <tbody>
                </tbody>
            </table>
        </x-slot>
    </x-modal>
@endsection

@section('scripts')
    <script>
        const modal = $('#show-product-varieties-modal');
        const varietiesTbale = modal.find('table');
        const modalTitle = modal.find('#title');
        const varietiesTbaleBody = varietiesTbale.find('tbody');
        const products = @json($products).data;

        function showModal(productId) {

            varietiesTbaleBody.empty();
            let productTitle = products.find((p) => p.id == productId).title;
            modalTitle.text('تنوع های' + ' ' + productTitle);
            modal.modal('show');

            $.ajax({
                url: '{{ route('admin.reports.load-varieties') }}',
                type: 'GET',
                data: {
                    product_id: productId
                },
                success: function(response) {

                    let dataNotFoundComponent = '';

                    if (response.data == null) {

                        dataNotFoundComponent += '<tr>';
                        dataNotFoundComponent += '<td colspan="4">';
                        dataNotFoundComponent += '<div class="text-center">';
                        dataNotFoundComponent +=
                            '<span class="text-danger">این محصول دارای تنوع نمی باشد !</span>';
                        dataNotFoundComponent += '</div>';
                        dataNotFoundComponent += '</td>';
                        dataNotFoundComponent += '</tr>';

                        varietiesTbaleBody.html(dataNotFoundComponent);

                        return;
                    }

                    let counter = 1;

                    response.data.variety_reports.forEach(varietyReport => {

                        let row = '';
                        let attr = '';

                        let salesCount = varietyReport.quantity.toLocaleString();
                        let totalSales = varietyReport.total.toLocaleString();

                        let variety = varietyReport.variety;

                        if (variety.color !== null) {
                            attr = variety.color.name;
                        }else {
                            attr = variety.attributes[0].label + ' : ' + variety.attributes[0].pivot.value;
                        }
                        
                        row += '<tr>';
                        row += `<td class="font-weight-bold">${counter}</td>`;
                        row += `<td>${attr}</td>`;
                        row += `<td>${salesCount}</td>`;
                        row += `<td>${totalSales}</td>`;
                        row += '</tr>';

                        varietiesTbaleBody.append(row);
                        counter++;

                    });
                }
            });
        }
    </script>

    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])
@endsection
