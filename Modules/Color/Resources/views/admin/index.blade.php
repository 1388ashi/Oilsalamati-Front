@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست همه رنگ ها']])
        <x-breadcrumb :items="$items" />
        @can('write_color')
            <x-create-button type="modal" target="addcolor" title="رنگ جدید" />
        @endcan
    </div>

    <x-card>
    <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
    <x-slot name="cardOptions"><x-card-options/></x-slot>
    <x-slot name="cardBody">
      <div class="row">
        <form action="{{ route('admin.colors.index') }}" method="GET" class="col-12">
          <div class="row">
            <div class="col-lg-4">
              <div class="form-group">
                <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="عنوان">
              </div>
            </div>
            <div class="col-lg-4">
              <div class="form-group">
                <input type="text" class="form-control" name="code" value="{{ request('code') }}" placeholder="کد رنگ">
              </div>
            </div>
            <div class="col-lg-4">
              <div class="form-group">
                <select name="status" class="form-control" id="status">
                  <option value="all">همه</option>
                  <option value="1" {{ request('status') == '1' ? 'selected' : null }}>فعال</option>
                  <option value="0" {{ request('status') == '0' ? 'selected' : null }}>غیر فعال</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-9 col-lg-8 col-md-6 col-12">
              <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
              <a href="{{ route('admin.store-transactions') }}" class="btn btn-danger btn-block">حذف همه فیلتر ها <i class="fa fa-close"></i></a>
            </div>
          </div>
        </form>
      </div>
    </x-slot>
  </x-card>

    <x-card>
        <x-slot name="cardTitle">رنگ ها ({{ $colors->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'عنوان', 'کد رنگ', 'رنگ', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($colors as $color)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $color->name }}</td>
                            <td style="direction: ltr">{{ $color->code }}</td>
                            <td>
                                <span class="d-flex justify-content-center">
                                    <div
                                        style="background-color:{{ $color->code }};width: 25px;height:25px;border-radius: 50%;border:1px black solid;">
                                    </div>
                                </span>
                            </td>
                            <td>@include('core::includes.status', ['status' => $color->status])</td>
                            <td>{{ verta($color->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @can('modify_color')
                                    @include('core::includes.edit-modal-button', [
                                        'target' => '#edit-color-' . $color->id,
                                    ])
                                @endcan
                                @can('delete_color')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $color,
                                        'route' => 'admin.colors.destroy',
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $colors->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @include('color::admin.edit')
    @include('color::admin.create')
@endsection

@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])

    <script>
        function updateColor(value) {
            var output = document.getElementById("output");
            output.textContent = "Selected color: " + value;
        }
    </script>

    <script>
        $('#status').select2({
            placeholder: 'انتخاب وضعیت'
        });
    </script>
@endsection
