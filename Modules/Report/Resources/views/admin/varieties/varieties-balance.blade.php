@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'گزارش موجودی تنوع ها']]" />
    </div>


    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.reports.varieties-balance') }}" method="GET">
                <div class="row">
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="id" placeholder="شناسه تنوع" value="{{ request('id') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="product_id" placeholder="شناسه محصول"
                            value="{{ request('product_id') }}" class="form-control" />
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
                        <a href="{{ route('admin.reports.varieties-balance') }}" class="btn btn-danger btn-block">حذف فیلتر
                            ها
                            <i class="fa fa-close" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">لیست تنوع ها ({{ number_format($varieties->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>عنوان محصول</th>
                        <th>شناسه محصول</th>
                        <th>تنوع</th>
                        <th>شناسه تنوع</th>
                        <th>موجودی انبار</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($varieties as $variety)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $variety->product->title }}</td>
                            <td>{{ $variety->product->id }}</td>
                            <td>
                                @if ($variety->attributes->isNotEmpty())
                                    {{ $variety->attributes->first()->pivot->value }}
                                @elseif ($variety->color_id != null)
                                    {{ $variety->color->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $variety->id }}</td>
                            <td>{{ $variety->store_balance }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 6])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $varieties->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
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
@endsection
