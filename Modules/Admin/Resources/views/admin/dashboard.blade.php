@extends('admin.layouts.master')
@section('styles')
<style>
    .entry {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
    }
    .entry:last-child {
        border-bottom: none;
    }
    .entry .date {
        color: #777;
        text-align: right;
    }
    .entry .action {
        color: #e74c3c;
        font-weight: bold;
    }
    .dot {
        height: 10px;
        width: 10px;
        background-color: #e74c3c;
        border-radius: 50%;
        display: inline-block;
        margin-left: 10px;
    }
    .dott {
        height: 10px;
        width: 10px;
        background-color: #33ff74;
        border-radius: 50%;
        display: inline-block;
        margin-left: 10px;
    }
    .log-date {
        border-bottom: 1px solid #ddd;
    }
    .log-date:last-of-type {
        border-bottom: none;
    }
</style>
@endsection
@section('content')
<div class="row">
    <div class="page-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i>
                داشبورد</a></li>
        </ol>
    </div>
</div>
@can('report')
<div class="row">
    <div class="col-xl-9 col-md-12 col-lg-12">
        <div class="row">
            <div class="col-xl-4 col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-9">
                                <a href="{{route('admin.orders.index')}}">
                                    <div class="mt-0 text-right">
                                        <span class="fs-16 font-weight-semibold"> تعداد کل سفارشات : </span>
                                        <p class="mb-0 mt-1 text-primary fs-20"> {{ number_format($ordersCount) }}</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-3">
                            <div class="icon1 bg-primary my-auto float-left">
                                <i class="fe fe-users"></i>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-9">
                                <a href="{{route('admin.orders.index')}}">
                                    <div class="mt-0 text-right">
                                        <span class="fs-16 font-weight-semibold"> تعداد سفارشات امروز :</span>
                                        <p class="mb-0 mt-1 text-pink  fs-20">{{ number_format($orderCountToday) }}</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-3">
                                <div class="icon1 bg-pink my-auto float-left">
                                    <i class="feather feather-box"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-12">
            <div class="card">
                <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <a href="{{route('admin.orders.index')}}">
                            <div class="mt-0 text-right">
                            <span class="fs-16 font-weight-semibold"> میزان فروش امروز :</span>
                                <p class="mb-0 mt-1 text-success fs-20"> {{ number_format($totalSalesToday) == 0 ? number_format($totalSalesToday) : number_format($totalSalesToday) . 'تومان'  }} </p>
                            </div>
                        </a>
                    </div>
                    <div class="col-3">
                    <div class="icon1 bg-success my-auto float-left">
                        <i class="feather feather-dollar-sign"></i>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card">  
                    <div class="card-header border-0">  
                        <div class="card-title">آمار فروش</div>  
                    </div>  
                    <div class="card-body">
                        <div class="row">  
                            <canvas id="barChart" height="150"></canvas>  
                            <select id="dataSelect">  
                                <option value="totalSales">فروش این ماه</option>  
                                <option value="month">ماه</option>  
                                <option value="year">فروش سال</option>  
                            </select>  
                        </div>  
                    </div>  
                </div>  
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-12">
        <div class="card">
        <div class="card-header  border-0">
            <a href="">
                <div class="card-title">آخرین فعالیت ها</div>
            </a>
        </div>
        <div class="card-body">
            <div class="list-group">
                @php($i = null)
                @foreach ($activityLogs as $activityLog)
                @php($i++)
                @if ($i % 2 == 0)
                <div class="list-group-item d-flex pt-3 pb-3 align-items-center border-0 p-0 m-0">
                    <div class="ml-3 ml-xs-0">
                        <div class="calendar-icon icons" style="line-height:0;">
                            <div class="date_time bg-pink-transparent"> <span class="date" style="line-height: normal;">{{verta($activityLog->created_at)->format('m/d H:i')}}</span></div>
                        </div>
                    </div>
                    <div class="ml-1">
                        <div class=" mb-1"><span class="font-weight-normal">{{$activityLog->description}}</span></div>
                    </div>
                </div>
                @else
                <div class="list-group-item d-flex pt-3 pb-3 align-items-center border-0 p-0 m-0">
                    <div class="ml-3 ml-xs-0">
                        <div class="calendar-icon icons" style="line-height:0;">
                            <div class="date_time bg-info-transparent "><span class="date" style="line-height: normal;">{{verta($activityLog->created_at)->format('m/d H:i')}}</span></div>
                        </div>
                    </div>
                    <div class="ml-1">
                        <div class=" mb-1"><span class="font-weight-normal">{{$activityLog->description}}</span></div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
       </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4 col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="card-title">کاربران</div>
            </div>
            <div class="card-body">
                <div style="width: 70%; margin: auto;">
                    <div class="d-flex justify-content-center">
                    <span class="fs-16">مجموع کاربران: <span class="font-weight-bold">{{$sumDataGender}}</span></span>
                </div>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="card-title">آخرین ورود ها به سیستم</div>
            </div>
            <div class="card-body">
                @foreach ($last_logins as $last_login)
                <div class="entry">
                    <div class="action"><span class="dot"></span>ورود به سیستم</div>
                    <div class="date">{{ $last_login->created_at->diffForHumans() }}</div>
                </div>
                <div class="d-flex justify-content-between">
                    <p class="font-weight-normall m-0">{{$last_login->tokenable->mobile}}</p>
                    <p class="text-muted text-end log-date mb-0">{{verta($last_login->created_at)->format('Y/m/d H:i')}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="card-title">آخرین بازدید های سایت</div>
                {{-- <div class="card-options">
                    <a href="{{ route('admin.   .index') }}" class="btn btn-outline-light ml-3">مشاهده
                        همه</a>
                </div> --}}
            </div>
            <div class="card-body">
                @foreach ($siteviewslist as $date => $value)
                <div class="entry log-date">
                    <div ><span class="dott"></span>{{verta($date)->format('Y/m/d')}}</div>
                    <div class="date">{{ $value }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header border-0 justify-content-between">
                <p class="card-title">نظرات محصولات ({{$newProductCommentsCount}})</p>
                <div class="card-options">
                    <a href="{{ route('admin.product-comments.index') }}" class="btn btn-outline-info ml-3">مشاهده
                    همه</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <table class="table table-vcenter table-striped text-nowrap table-bordered border-bottom">
                            <thead class="thead-light">
                            <tr>
                            <th class="text-center">کاربر</th>
                            <th class="text-center">محصول</th>
                            <th class="text-center">وضعیت</th>
                            <th class="text-center">تاریخ</th>
                            <th class="text-center">مشاهده</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($comments as $comment)
                            <tr class="text-center">
                                <td>{{ $comment->creator->full_name ? $comment->creator->full_name : $comment->creator->mobile}}</td>
                                <td>{{Str::limit($comment->product->title,15,'...')}}</td>

                                <td>
                                    <span class="badge badge-{{ config('productcomment.status_color.' . $comment->status) }}">
                                    {{ config('productcomment.statuses.' . $comment->status) }}
                                    </span>
                                </td>
                                <td>{{ verta($comment->created_at)->format('Y/m/d') }}</td>

                                <td>
                                    <button
                                        class="btn btn-sm btn-info btn-icon text-white"
                                        data-target="#show-comment-detail-modal-{{ $comment->id }}"
                                        data-toggle="modal">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            @include('core::includes.data-not-found-alert', ['colspan' => 9])

                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header border-0 justify-content-between">
                <p class="card-title"> نظرات بلاگ خوانده نشده ({{$newBlogCommentsCount}})</p>
                <div class="card-options">
                    <a href="{{ route('admin.post-comments.all') }}" class="btn btn-outline-info ml-3">مشاهده
                    همه</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <table class="table table-vcenter table-striped text-nowrap table-bordered border-bottom">
                            <thead class="thead-light">
                            <tr>
                            <th class="text-center">کاربر</th>
                            <th class="text-center">مطلب</th>
                            <th class="text-center">وضعیت</th>
                            <th class="text-center">تاریخ</th>
                            <th class="text-center">مشاهده</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($blogComments as $blogComment)
                            <tr class="text-center">
                                <td>{{ $blogComment->name ?? '-'}}</td>
                                <td>{{Str::limit( $blogComment->commentable->title,20,'...')}}</td>
                                <td>
                                    <span class="badge badge-{{ config('comment.status_color.' . $blogComment->status) }}">
                                    {{ config('comment.statuses.' . $blogComment->status) }}
                                    </span>
                                </td>
                                <td>{{ verta($blogComment->created_at)->format('Y/m/d') }}</td>

                                <td>
                                    <a
                                        href="{{route("admin.post-comments.show", $blogComment)}}"
                                        class="btn btn-sm btn-info btn-icon text-white"
                                        data-toggle="tooltip"
                                        data-original-title="نمایش">
                                        <i class="fa fa-eye ? 'mr-1' : null }}"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            @include('core::includes.data-not-found-alert', ['colspan' => 9])

                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    @endcan
    @include('productcomment::admin.includes.show-comment-detail-modal')
@endsection
@section('scripts')
    <script src="{{asset('assets/js/pieChart/chart.js')}}"></script>
  <script>
     function assignStatus(status, commentId) {
        $('#status-' + commentId).attr('value', status);
        $('#assign-status-form-' + commentId).submit();
    }
    var ctx = document.getElementById('pieChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: @json($dataGender['labels']),
            datasets: [{
                data: @json($dataGender['data']),
                backgroundColor: [
                    'red',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                ],
                borderColor: [
                    'red',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                ],
                borderWidth: 1
            }]
        },
    });


    document.addEventListener('DOMContentLoaded', function() {
        const salesData = {
            totalSales: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
            month: ['اسفند','بهمن','دی','آذر','آبان','مهر','شهریور','مرداد','تیر','خرداد','اردیبهشت','فروردین'],
            year: [1399,1400, 1401,1402,1403]
        };

        const amountData = {
            totalSales:[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
            month: [10110, 20110, 15110, 30110, 25110, 11111, 15022,33232,44344,55525,11112,23532],
            year: [2399, 2110, 2500, 2400, 2200]
        };
        const barCtx = document.getElementById("barChart").getContext('2d');
        let myBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: salesData.totalSales,
                datasets: [{
                    label: 'نمودار',
                    data: amountData.totalSales,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function updateChart(selectedValue) {
            myBarChart.data.labels = salesData[selectedValue];
            myBarChart.data.datasets[0].data = amountData[selectedValue];
            myBarChart.update();
        }

        document.getElementById('dataSelect').addEventListener('change', function() {
            updateChart(this.value);
        });

        // بارگذاری اولیه نمودار
        updateChart('totalSales');
    });

  </script>
@endsection
