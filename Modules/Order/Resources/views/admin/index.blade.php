@extends('admin.layouts.master')
@section('styles')
    <style>
        .test {
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .inactive {
            opacity: 0.5;
        }

        .hidden {
            display: none;
            float: left;
        }

        .add {
            display: flex;
            float: left;
        }
    </style>
@endsection
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست سفارشات']]" />
    </div>
    @include('order::admin.includes.filter-form')
    <!-- row opened -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-0">
                    <p class="card-title">لیست همه سفارشات ({{ $orders->total() }})</p>
                    <x-card-options />
                </div>
                <div class="card-body">
                    <div id="buttonsRow" class="hidden mb-3">
                        <button type="button" class="btn btn-sm mr-2 btn-warning" data-target="#changeStatus"
                            data-toggle="modal">تغییر وضعیت</button>
                        <button type="submit" class="btn btn-sm mr-2 btn-info">PDF<i
                                class="fa fa-file-pdf-o mr-1"></i></button>
                        <form action="{{ route('admin.orders.print') }}" method="POST">
                            @csrf
                            <input hidden name="ids[]" id="printValue">
                            <button type="submit" class="btn btn-sm mr-2 btn-purple">چاپ<i
                                    class="si si-printer mr-1"></i></button>
                        </form>
                    </div>
                    <div class="mb-3">
                        @php
                            $statusColors = [
                                'wait_for_payment' => 'btn-rss',
                                'new' => 'btn-primary',
                                'in_progress' => 'btn-secondary',
                                'delivered' => 'btn-success',
                                'canceled' => 'btn-pinterest',
                                'failed' => 'btn-youtube',
                                'reserved' => 'btn-info',
                                'canceled_by_user' => 'btn-danger',
                            ];

                            $mainClasses = 'status-btn btn btn-sm';
                        @endphp
                        @foreach ($allOrderStatuses as $statusName)
                            <a href="{{ route('admin.orders.index', ['status' => $statusName]) }}"
                                class="status-btn btn btn-sm {{ $statusColors[$statusName] }} {{ request('status') == $statusName ? 'test' : 'inactive' }}">
                                {{ __('statuses.' . $statusName) }}
                            </a>
                        @endforeach
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-dark">برداشتن فیلتر
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                    @include('components.errors')
                    <div class="table-responsive">
                        <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <table class="table table-bordered text-nowrap text-center">
                                    <form action="{{ route('admin.orders.changeStatusSelectedOrders') }}" method="post"
                                        id="myForm">
                                        <input type="hidden" id="output" name="status" value="">
                                        @csrf
                                        <thead>
                                            <tr>
                                                <th class="wd-20p" style="width: 5%;"><input type="checkbox" id="check_all">
                                                </th>
                                                <th class="border-top">شناسه</th>
                                                <th class="border-top">گیرنده</th>
                                                <th class="border-top">تعداد ایتم ها</th>
                                                <th class="border-top">وضعیت</th>
                                                <th class="border-top">مبلغ کل سفارش</th>
                                                <th class="border-top">تاریخ ثبت</th>
                                                <th class="border-top">عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            @forelse($orders as $order)
                                                <tr>
                                                    <td><input type="checkbox" class="checkbox toggleCheckbox"
                                                            data-id="{{ $order->id }}" name="ids[]"
                                                            value="{{ $order->id }}"
                                                            onchange="toggleSpeceficButtons({{ $order->id }})">
                                                    </td>

                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->receiver }}</td>
                                                    <td>{{ $order->total_items_count ? $order->total_items_count : '-' }}
                                                    </td>
                                                    <td>@include('core::includes.statusOrder', [
                                                        'status' => $order->status,
                                                    ])</td>
                                                    <td>{{ number_format($order->total_invoices_amount) }}</td>
                                                    <td>{{ verta($order->created_at)->format('Y/m/d H:i') }}</td>
                                                    <td>
                                                        @include('core::includes.edit-icon-button', [
                                                            'route' => 'admin.orders.edit',
                                                            'model' => $order,
                                                        ])
                                                        @include('core::includes.show-icon-button', [
                                                            'route' => 'admin.orders.show',
                                                            'model' => $order,
                                                        ])
                                                    </td>
                                                </tr>
                                            @empty
                                                @include('core::includes.data-not-found-alert', [
                                                    'colspan' => 8,
                                                ])
                                            @endforelse
                                        </tbody>
                                    </form>
                                </table>
                                {{ $orders->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="changeStatus">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title font-weight-bolder">تغییر وضعیت</p>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-12">
                        <div class="form-group">
                            <select class="form-control status2" id="status2" required>
                                <option value="">- انتخاب کنید -</option>
                                <option value="in_progress">در حال پردازش</option>
                                <option value="delivered">ارسال شده</option>
                                <option value="new">در انتظار تکمیل</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button id="submitButton" type="submit" class="btn btn-primary text-right item-right">ثبت</button>
                    <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @include('order::admin.includes.filter-form-scripts')
    @include('order::admin.includes.index-scripts')

    <script>
        $('#status2').select2({
            placeholder: 'انتخاب وضعیت'
        })
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });


        $('.status-btn').on('click', function() {
            $('.status-btn').removeClass('test').addClass('inactive');
            $(this).removeClass('inactive').addClass('test');
        });
        $(document).ready(function() {
            var $statusSelect = $('#status2');
            var $outputInput = $('#output');

            $statusSelect.on('change', function() {
                var selectedValue = $statusSelect.val();
                $outputInput.val(selectedValue);
                console.log('Selected value: ' + selectedValue);
            });
            $('#check_all').on('click', function(e) {
                if ($('#check_all').is(':checked', true)) {
                    $(".checkbox").attr('checked', true);
                } else {
                    $(".checkbox").attr('checked', false);
                }
            });

            $('#check_all').click(() => {
                $('#buttonsRow').toggleClass('hidden');
                $('#buttonsRow').toggleClass('add');
            });

            $('.toggleCheckbox').on('click', function() {
                const anyChecked = $('.toggleCheckbox:checked').length > 0;

                if (anyChecked) {
                    $('#buttonsRow').removeClass('hidden').addClass('add');
                } else {
                    $('#buttonsRow').addClass('hidden').removeClass('add');
                }
            });

        });

        document.querySelectorAll('.status-btn').forEach(item => {
            item.addEventListener('click', event => {
                document.querySelectorAll('.test').forEach(link => {
                    $(selector).hasClass(className);
                    link.classList.add('inactive');
                });
                item.classList.remove('inactive');
            });
        });

        function toggleSpeceficButtons(id) {

            let arr = []
            arr.push(id);
            let printVal = document.getElementById('printValue');
            console.log(printVal.length);
            if (printVal.length == 0) {
                $('#printValue').val(arr)
            } else {
                printVal.forEach(el => {
                    arr.push(el)
                })
            }
        }
    </script>
@endsection
