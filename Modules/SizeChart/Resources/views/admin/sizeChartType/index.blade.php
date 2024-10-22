@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item active">نوع سایز چارت</li>
        </ol>
        <div class="">
            <div class="d-flex align-items-center flex-wrap text-nowrap">
                <button data-toggle="modal" data-target="#addsizeChartType" class="btn btn-primary ">
                    ایجاد نوع چارت جدید
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- row opened -->
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title">نوع سایز چارت ها ({{ $sizeChartTypes->total() }})</p>
            <x-card-options />
        </div>
        <div class="card-body">
            @include('components.errors')
            <div class="table-responsive">
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <table
                            class="table table-vcenter table-striped text-nowrap table-bordered border-bottom text-center">
                            <thead>
                                <tr>
                                    <th class="border-top">ردیف</th>
                                    <th class="border-top">عنوان</th>
                                    <th class="border-top">تاریخ ثبت</th>
                                    <th class="border-top">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sizeChartTypes as $sizeChartType)
                                    <tr>
                                        <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $sizeChartType->name }}</td>
                                        <td>{{ verta($sizeChartType->created_at)->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm btn-icon btn-sm"
                                                data-toggle="modal" onclick="showDescriptionModal({{ $sizeChartType }})"
                                                data-original-title="نوع ها">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            @include('core::includes.edit-modal-button', [
                                                'target' => '#edit-sizeChartType-' . $sizeChartType->id,
                                            ])

                                            @include('core::includes.delete-icon-button', [
                                                'model' => $sizeChartType,
                                                'route' => 'admin.sizecharttype.destroy',
                                                'disabled' => $sizeChartType->isDeletable(),
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <p class="text-danger"><strong>در حال حاضر هیچ نوع سایزی یافت نشد!</strong></p>
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                        {{ $sizeChartTypes->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('sizechart::admin.sizeChartType.create')
    @include('sizechart::admin.sizeChartType.edit')
    @include('sizechart::admin.sizeChartType.values')
    <!-- row closed -->
@endsection
@section('scripts')
    <script>
        function showDescriptionModal(sizeChartType) {
            let sizeChartTypeId = sizeChartType.id;
            let modal = $('#showDescriptionModal');

            // خالی کردن محتوای قبلی
            modal.find('#description').empty();

            // حلقه برای افزودن مقادیر جدید
            sizeChartType.values.forEach(value => {
                modal.find('#description').append(`<div class="mb-1">${value.name || '-'}</div>`);
            });

            modal.modal('show');
        }
        let index = 0;
        $(document).ready(function() {
            const addPurchaseItemBtn = $(".addPurchaseItemBtn");
            const removePurchaseItemButton = $(".removePurchaseItemButton");

            addPurchaseItemBtn.on('click', function(event) {
                event.preventDefault(); // جلوگیری از ارسال فرم
                addNewInput();
            });


            function addNewInput() {
                const newPurchaseItemInputs = $(`
            <div class="row input-container">
                <button class="btn btn-danger neg btn-sm btn-icon deleteRowButton mb-1 ml-1" type="button">-</button>
                <input type="text" style="width: 91%" class="numberInput form-control mb-2" placeholder="نوع" name="values[]" />
            </div>
        `);

                $('#addType').append(newPurchaseItemInputs);
                const addPurchaseItemButtonTop = $(".addPurchaseItemButton");
                const deleteRow = $(".deleteRow");


                addPurchaseItemButtonTop.on('click', function(event) {
                    event.preventDefault(); // جلوگیری از ارسال فرم
                    addNewInput();
                });
                deleteRow.on('click', function(e) {
                    if (index === 0) {
                        removePurchaseItemButton.attr('disabled', true);
                        removePurchaseItemButton.prop('disabled', true);
                    } else {
                        $(this).closest('.row').remove();
                        index--;
                        toggleRemoveButton();
                    }
                });
                // افزودن رویداد به دکمه حذف جدید
                newPurchaseItemInputs.find('.deleteRowButton').on('click', function(e) {
                    $(this).closest('.row').remove();
                    index--;
                    toggleRemoveButton();
                });

                toggleRemoveButton();
            }

            function toggleRemoveButton() {
                // اگر هیچ ورودی وجود ندارد، دکمه حذف غیرفعال می‌شود
                if (index === 0) {
                    removePurchaseItemButton.prop('disabled', true);
                } else {
                    removePurchaseItemButton.prop('disabled', false);
                }
            }

        });
    </script>
@endsection
