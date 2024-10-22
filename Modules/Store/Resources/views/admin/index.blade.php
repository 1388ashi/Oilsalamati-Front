@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [['title' => 'انبار', 'route_link' => null]];
        @endphp
        <x-breadcrumb :items="$items" />
        <div style="display: flex; gap: 8px;">
            <button id="increment-store-btn" class="btn btn-outline-success btn-sm">افزایش موجودی</button>
            <button id="decrement-store-btn" class="btn btn-outline-danger btn-sm">کاهش موجودی</button>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <div class="row">
                <form action="{{ route('admin.store-transactions') }}" method="GET" class="col-12">
                    <div class="row">

                        <x-search-inputs.product-search cssClasses="col-lg-3" productInputId="filter-products"
                            varietyInputId="filter-varieties" productInputName="product_id" varietyInputName="variety_id"
                            hasLabel="true" :products="$products" />

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>شناسه :</label>
                                <input type="number" class="form-control" name="id" value="{{ request('id') }}">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>نوع تغییرات :</label>
                                <select class="form-control" name="type">
                                    <option value="">همه</option>
                                    @foreach (config('store.transaction_types') as $name => $label)
                                        <option value="{{ $name }}"
                                            {{ request('type') == $name ? 'selected' : null }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-xl-9 col-lg-8 col-md-6 col-12">
                            <button class="btn btn-primary btn-block" type="submit">جستجو <i
                                    class="fa fa-search"></i></button>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="{{ route('admin.store-transactions') }}" class="btn btn-danger btn-block">حذف همه فیلتر
                                ها <i class="fa fa-close"></i></a>
                        </div>

                    </div>
                </form>
            </div>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">تراکنش های انبار</x-slot>
        <x-slot name="cardOptions">
            @if ($varietyBalance)
                <div class="card-options">
                    <button class="btn btn-sm btn-pink fs-16">موجودی تنوع : {{ $varietyBalance }}</button>
                </div>
            @elseif($productBalance)
                <div class="card-options">
                    <button class="btn btn-sm btn-pink fs-16">موجودی محصول : {{ $productBalance }}</button>
                </div>
            @else()
                <x-card-options />
            @endif
        </x-slot>
        <x-slot name="cardBody">
            <div class="row">
                @php
                    $types = [
                        'decrement' => [
                            'class' => 'badge badge-danger-light',
                            'title' => 'کاهش',
                        ],
                        'increment' => [
                            'class' => 'badge badge-success-light',
                            'title' => 'افزایش',
                        ],
                    ];
                @endphp
                <x-table-component>
                    <x-slot name="tableTh">
                        <tr>
                            @php($tableTh = ['ردیف', 'شناسه', 'محصول', 'توضیح', 'تعداد', 'نوع تغییرات', 'تاریخ ثبت'])
                            @foreach ($tableTh as $th)
                                <th>{{ $th }}</th>
                            @endforeach
                        </tr>
                    </x-slot>
                    <x-slot name="tableTd">
                        @forelse ($storeTransactions as $transaction)
                            <tr>
                                <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                <td>{{ $transaction->id }}</td>
                                <td>{{ $transaction->store->variety->product->title }}</td>
                                <td style="white-space: wrap;">{{ $transaction->description }}</td>
                                <td>{{ $transaction->quantity }}</td>
                                <td>
                                    <span
                                        class="{{ $types[$transaction->type]['class'] }}">{{ $types[$transaction->type]['title'] }}</span>
                                </td>
                                <td>{{ verta($transaction->created_at)->format('Y/m/d H:i') }}</td>
                            </tr>
                        @empty
                            @include('core::includes.data-not-found-alert', ['colspan' => 7])
                        @endforelse
                    </x-slot>
                    <x-slot
                        name="extraData">{{ $storeTransactions->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
                </x-table-component>
            </div>
        </x-slot>
    </x-card>

    <x-modal id="increase-decrease-modal" size="md">
        <x-slot name="title"></x-slot>
        <x-slot name="body">
            <form action="{{ route('admin.stores.store') }}" method="POST">

                @csrf
                <input type="hidden" name="type">

                <div class="row">

                    <x-search-inputs.product-search cssClasses="col-12" productInputId="store-form-products"
                        varietyInputId="store-form-varieties" productInputName="product_id" varietyInputName="variety_id"
                        hasLabel="false" :products="$products" />

                    <div class="col-12">
                        <div class="form-group">
                            <input class="form-control" placeholder="تعداد" name="quantity" />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <textarea class="form-control" row="2" placeholder="توضیحات" name="description"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer justify-content-center mt-2">
                    <button class="btn btn-primary" type="submit">ثبت و ذخیره</button>
                    <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
                </div>

            </form>
        </x-slot>
    </x-modal>
@endsection

@section('scripts')
    <x-search-inputs.product-search-scripts productInputId="filter-products" varietyInputId="filter-varieties" />
    <x-search-inputs.product-search-scripts productInputId="store-form-products" varietyInputId="store-form-varieties" />

    <script>
        $(document).ready(() => {
            $('#increment-store-btn').click(() => showModal('increment'));
            $('#decrement-store-btn').click(() => showModal('decrement'));
        });

        function showModal(type) {
            const modal = $('#increase-decrease-modal');
            let text = type == 'increment' ? 'اضافه کردن به انبار' : 'کم کردن از انبار';
            modal.find('.modal-title').text(text);
            modal.find('#type').val(type);
            modal.modal('show');
        }
    </script>
@endsection
