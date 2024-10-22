@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item active">سایز چارت ها</li>
        </ol>
        <div class="">
            <div class="d-flex align-items-center flex-wrap text-nowrap">
                <a href="{{ route('admin.sizecharttype.index') }}" class="btn btn-secondary ">
                    انواع سایز چارت
                    <i class="fa fa-plus"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- row opened -->
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title">سایز چارت ها </p>
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
                                    {{-- <th class="border-top">چارت</th> --}}
                                    <th class="border-top">تاریخ ثبت</th>
                                    {{-- <th class="border-top">عملیات</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sizeCharts as $sizeChart)
                                    <tr>
                                        <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $sizeChart->title }}</td>
                                        {{-- <td>{{ $sizeChart->chart }}</td> --}}
                                        <td>{{ verta($sizeChart->created_at)->format('Y/m/d H:i') }}</td>
                                        {{-- <td>
                          @can('edit sizeChart')
                          @include('core::includes.edit-modal-button',[
                            'target' => "#edit-color-" . $color->id
                          ])
                          @endcan

                          @can('delete sizeChart')

                            @include('core::includes.delete-icon-button',[
                              'model' => $color,
                              'route' => 'admin.colors.destroy',
                              'disabled' => false
                            ])

                          @endcan
                        </td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <p class="text-danger"><strong>در حال حاضر هیچ سایزی یافت نشد!</strong></p>
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- row closed -->
@endsection
