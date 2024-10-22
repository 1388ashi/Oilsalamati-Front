@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item active">لیست برند ها</li>
        </ol>
        @can('write_brand')
            <x-create-button type="modal" target="createBrandModal" title="برند جدید" />
        @endcan
    </div>
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title">برند ها ({{ $totalBrands }})</p>
            <x-card-options />
        </div>
        <div class="card-body">
            @include('components.errors')
            <div class="table-responsive">
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <table class="table table-vcenter table-striped text-nowrap table-bordered border-bottom">
                            <thead>
                                <tr>
                                    <th class="text-center">ردیف</th>
                                    <th class="text-center">نام برند</th>
                                    <th class="text-center">تصویر</th>
                                    <th class="text-center">سازنده</th>
                                    <th class="text-center">آخرین ویرایش کننده</th>
                                    <th class="text-center">وضعیت</th>
                                    <th class="text-center">نمایش در صفحه اصلی</th>
                                    <th class="text-center">تاریخ ثبت</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($brands as $brand)
                                    <tr>
                                        <td class="text-center font-weight-b0ld">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $brand->name }}</td>
                                        <td class="text-center m-0 p-0">
                                            @if ($brand->image)
                                                <figure class="figure my-2">
                                                    <a target="_blank"
                                                        href="{{ Storage::url($brand->image['uuid'] . '/' . $brand->image['file_name']) }}">
                                                        <img src="{{ Storage::url($brand->image['uuid'] . '/' . $brand->image['file_name']) }}"
                                                            class="img-thumbnail" alt="image" width="50"
                                                            style="max-height: 32px;" />
                                                    </a>
                                                </figure>
                                            @else
                                                <span> - </span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $brand->creator->name }}</td>
                                        <td class="text-center">{{ $brand->updater->name }}</td>
                                        <td class="text-center">@include('core::includes.status', ['status' => $brand->status])</td>
                                        <td class="text-center">
                                            @if ($brand->show_index)
                                                <span><i class="text-success fs-26 fa fa-check-circle-o"></i></span>
                                            @else
                                                <span><i class="text-danger fs-26 fa fa-close"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ verta($brand->created_at)->format('Y/m/d H:i') }}</td>
                                        <td class="text-center">

                                            <button class="btn btn-sm btn-icon btn-primary"
                                                onclick="showBrandDescriptionModal('{{ $brand->description }}')"
                                                data-toggle="tooltip" data-original-title="توضیحات">
                                                <i class="fa fa-book"></i>
                                            </button>

                                            @can('modify_brand')
                                                @include('core::includes.edit-modal-button', [
                                                    'target' => '#editBrandModal-' . $brand->id,
                                                ])
                                            @endcan

                                            @can('delete_brand')
                                                @include('core::includes.delete-icon-button', [
                                                    'model' => $brand,
                                                    'route' => 'admin.brands.destroy',
                                                    'disabled' => !$brand->isDeletable(),
                                                ])
                                            @endcan

                                        </td>
                                    </tr>
                                @empty

                                    @include('core::includes.data-not-found-alert', ['colspan' => 9])
                                @endforelse
                            </tbody>
                        </table>
                        {{ $brands->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('write_brand')
        @include('brand::admin.includes.create-modal')
    @endcan

    @can('modify_brand')
        @include('brand::admin.includes.edit-modal')
    @endcan

    @include('brand::admin.includes.show-description-modal')
@endsection

@section('scripts')
    <script>
        function showBrandDescriptionModal(description) {
            let modal = $('#showDescriptionModal');
            modal.find('#description').text(description ?? '-');
            modal.modal('show');
        }
    </script>
@endsection
