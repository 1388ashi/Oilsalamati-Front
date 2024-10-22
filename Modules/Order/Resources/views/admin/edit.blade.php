@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست سفارشات', 'route_link' => 'admin.orders.index'], ['title' => 'ویرایش سفارش']]" />
		<a href="{{ route('admin.orders.show', $order) }}" class="btn btn-warning text-dark font-weight-bold btn-sm">بازگشت به جزئیات سفارش</a>
    </div>

    <form action="{{ route('admin.orderUpdater.showcase', $order->id) }}" id="UpdateOrderItemsQuantityForm" method="POST">

        @csrf

        <input type="hidden" name="OrderItemsAddCarts" id="OrderItemsAddCarts">
        <input type="hidden" name="OrderItemsDeleteCarts" id="OrderItemsDeleteCarts">

        

        @if ($errors->any())
            <x-card>
                <x-slot name="cardTitle">خطا ها</x-slot>
                <x-slot name="cardOptions"><x-card-options /></x-slot>
                <x-slot name="cardBody">
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </x-slot>
            </x-card>
        @endif

        <div class="row">
            <x-card col="col-xl-8">
                <x-slot name="cardTitle">ویرایش سفارش</x-slot>
                <x-slot name="cardOptions"><x-card-options /></x-slot>
                <x-slot name="cardBody">
                    <div class="row mb-3">
                        <div class="col-12 col-xl-6">
                            <div class="form-group">
                                <label for="address_id">انتخاب آدرس جدید :</label>
                                <select name="newAddress_id" id="address_id" class="form-control">
                                    <option value=""></option>
                                    @foreach ($addresses as $address)
                                        <option value="{{ $address->id }}">{{ $address->address }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
    
                        <div class="col-12 col-xl-6">
                            <div class="form-group">
                                <label for="shipping_id">انتخاب حمل و نقل جدید :</label>
                                <select name="newShipping_id" id="shipping_id" class="form-control">
                                    <option value=""></option>
                                    @foreach ($shippings as $shipping)
                                        <option value="{{ $shipping->id }}">{{ $shipping->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
    
                        <div class="col-12 col-xl-6">
                            <div class="form-group">
                                <label for="pay_type">انتخاب نوع پرداخت :</label>
                                <select name="pay_type" id="pay_type" class="form-control">
                                    <option value=""></option>
                                    <option value="gateway">درگاه</option>
                                    <option value="wallet">کیف پول</option>
                                    <option value="both">هر دو</option>
                                </select>
                            </div>
                        </div>
    
                        <div class="col-12 col-xl-6">
                            <div class="form-group">
                                <label for="payment_driver">انتخاب درگاه :</label>
                                <select name="payment_driver" id="payment_driver" class="form-control">
                                    <option value=""></option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver }}">{{ $driver }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
    
                        <div class="col-12 col-xl-6">
                            <div class="form-group">
                                <label for="payment_driver">انتخاب محصول :</label>
                                <select class="form-control search-product-ajax select2" id="products"></select>
                            </div>
                        </div>
    
                        <div class="col-12 col-xl-6">
                            <div class="form-group">
                                <label for="payment_driver">انتخاب تنوع :</label>
                                <select id="variety" class="form-control select2"></select>
                            </div>
                        </div>
    
                    </div>
    
                    <x-table-component id="NewItemsTable">
                        <x-slot name="tableTh">
                            <tr>
                                <th>محصول</th>
                                <th>تنوع</th>
                                <th>قیمت (تومان)</th>
                                <th>تعداد</th>
                                <th>عملیات</th>
                            </tr>
                        </x-slot>
                        <x-slot name="tableTd"></x-slot>
                    </x-table-component>
    
                </x-slot>
            </x-card>
    
            <x-card col="col-xl-4">
                <x-slot name="cardTitle">جزئیات سفارش</x-slot>
                <x-slot name="cardOptions"><x-card-options /></x-slot>
                <x-slot name="cardBody">
                    <div class="row">
    
                        <div class="col-12 my-1 fs-16">
                            <strong>شناسه سفارش : </strong><span>{{ $order->id }}</span>
                        </div>
    
                        <div class="col-12 my-1 fs-16">
                            <strong>گیرنده : </strong><span>{{ $order->customer->full_name ?? '-' }}</span>
                        </div>
    
                        <div class="col-12 my-1 fs-16">
                            <strong>موجودی کیف پول :
                            </strong><span>{{ $order->customer->wallet ? number_format($order->customer->wallet->balance) : 0 }}
                                تومان</span>
                        </div>
    
                        <div class="col-12 my-1 fs-16">
                            <strong>آدرس فعلی : </strong><span>{{ json_decode($order->address)->address }}</span>
                        </div>
    
                        <div class="col-12 my-1 fs-16">
                            <strong>حمل و نقل فعلی : </strong><span>{{ $order->shipping->name }}</span>
                        </div>
    
                    </div>
                </x-slot>
            </x-card>
        </div>

        {{-- <x-card>
            <x-slot name="cardTitle">افزودن قلم جدید</x-slot>
            <x-slot name="cardOptions"><x-card-options /></x-slot>
            <x-slot name="cardBody">
                <div class="row">

                    <div class="col-12 col-xl-6">
                        <div class="form-group">
                            <select class="form-control search-product-ajax select2" id="products"></select>
                        </div>
                    </div>

                    <div class="col-12 col-xl-6">
                        <div class="form-group">
                            <select id="variety" class="form-control select2"></select>
                        </div>
                    </div>

                </div>

                <x-table-component id="NewItemsTable">
                    <x-slot name="tableTh">
                        <tr>
                            <th>محصول</th>
                            <th>تنوع</th>
                            <th>قیمت (تومان)</th>
                            <th>تعداد</th>
                            <th>عملیات</th>
                        </tr>
                    </x-slot>
                    <x-slot name="tableTd"></x-slot>
                </x-table-component>

            </x-slot>
        </x-card> --}}

        <x-card>
            <x-slot name="cardTitle">ویرایش اقلام سفارش</x-slot>
            <x-slot name="cardOptions"><x-card-options /></x-slot>
            <x-slot name="cardBody">

                <x-table-component>
                    <x-slot name="tableTh">
                        <tr>
                            <th>ردیف</th>
                            <th>شناسه</th>
                            <th>محصول</th>
                            <th>کمپین</th>
                            <th>مبلغ واحد (تومان)</th>
                            <th>تخفیف واحد (تومان)</th>
                            <th>تعداد</th>
                            <th>مبلغ کل (تومان)</th>
                            <th>تخفیف کل (تومان)</th>
                            <th>مبلغ با تخفیف (تومان)</th>
                            <th>ویرایش تعداد</th>
                        </tr>
                    </x-slot>
                    <x-slot name="tableTd">
                        @php($totalPrice = 0)
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->variety->title }}</td>
                                <td>{{ $item->flash->title ?? '-' }}</td>
                                <td>{{ number_format($item->amount) }}</td>
                                <td>{{ number_format($item->discount_amount) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->amount * $item->quantity) }}</td>
                                <td>{{ number_format($item->discount_amount * $item->quantity) }}</td>
                                <td>{{ number_format(($item->amount - $item->discount_amount) * $item->quantity) }}</td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-danger decrease-quantity">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                        <input type="text" onkeypress="return /[0-9]/i.test(event.key)"
                                            class="form-control mx-1 text-center NewProductQuantity quantity-input"
                                            data-variety_id="{{ $item->variety_id }}" value="{{ $item->quantity }}"
                                            style="max-width: 50px; max-height: 30px; appearance: none;">
                                        <button type="button" class="btn btn-sm btn-icon btn-success increase-quantity">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @php($totalPrice += ($item->amount - $item->discount_amount) * $item->quantity)
                        @endforeach
                    </x-slot>
                    <x-slot name="extraData">
                        <span><strong>جمع کل : </strong> {{ number_format($totalPrice) }} تومان</span>
                    </x-slot>
                </x-table-component>

            </x-slot>
        </x-card>

        <div class="row" style="margin-bottom: 100px;">
            <div class="col-12">
                <button id="Invoicing" type="button" class="btn btn-lg btn-primary btn-block">صدور صورت حساب</button>
            </div>
        </div>

    </form>
@endsection

@section('scripts')
    <script>
        $('#shipping_id').select2({
            placeholder: 'حمل و نقل جدید را انتخاب کنید'
        });
        $('#address_id').select2({
            placeholder: 'آدرس جدید را انتخاب کنید'
        });
        $('#products').select2({
            placeholder: 'محصول را جستجو کنید'
        });
        $('#variety').select2({
            placeholder: 'ابتدا محصول را انتخاب کنید'
        });
        $('#pay_type').select2({
            placeholder: 'روش پرداخت را انتخاب کنید'
        });
        $('#payment_driver').select2({
            placeholder: 'درگاه پرداخت را در صورت نیاز انتخاب کنید'
        });
    </script>

    <script>
        $('.search-product-ajax').select2({
            ajax: {
                url: '{{ route('admin.products.search') }}',
                dataType: 'json',
                processResults: (response) => {
                    let products = response.data.products || [];

                    return {
                        results: products.map(product => ({
                            id: product.id,
                            title: product.title,
                        })),
                    };
                },
                cache: true,
            },
            placeholder: 'محصول را جستجو کنید',
            templateResult: (repo) => {
                if (repo.loading) {
                    return "در حال بارگذاری...";
                }

                let $container = $(
                    "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'></div>" +
                    "</div>" +
                    "</div>"
                );

                let text = repo.title;
                $container.find(".select2-result-repository__title").text(text);

                return $container;
            },
            minimumInputLength: 1,
            templateSelection: (repo) => {
                return repo.id ? repo.title : repo.text;
            }
        });

        $(document).ready(() => {

            let orderItems = @json($order->items);

            let addCarts = [];
            let deleteCarts = [];

            $('.decrease-quantity').on('click', function() {
                const input = $(this).closest('tr').find('.quantity-input');
                let currentValue = parseInt(input.val());
                if (currentValue > 0) {
                    input.val(currentValue - 1);
                }
            });

            $('.increase-quantity').on('click', function() {
                const input = $(this).closest('tr').find('.quantity-input');
                let currentValue = parseInt(input.val());
                input.val(currentValue + 1);
            });


            let productTitle = '';
            $('#products').on('select2:select', (e) => {
                const selectedProduct = e.params.data;
                productTitle = selectedProduct.title;
                let productId = selectedProduct.id;
                $.ajax({
                    url: '{{ route('admin.stores.load-varieties') }}',
                    type: 'POST',
                    data: {
                        product_id: productId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {

                        let varieties = response.varieties;
                        let options = '<option value=""></option>';

                        varieties.forEach((variety) => {

                            let id = variety.id;
                            let balance = variety.store.balance ?? 0;
                            let price = variety.final_price.amount ?? 0;
                            let attr = null;

                            if (variety.attributes[0] != undefined) {
                                attr = variety.attributes[0].pivot.value;
                            }

                            if (variety.color != null) {
                                attr = variety.color.name;
                            }

                            let optionTitle = `شناسه: ${id}`;

                            if (attr) {
                                optionTitle += ` | ${attr}`;
                            }

                            optionTitle += ` | موجودی: ${balance}`;

                            let optionValue = variety.id;

                            options +=
                                `<option value="${optionValue}" data-balance="${balance}" data-price="${price}">${optionTitle}</option>`

                        });

                        $('#variety').html(options);

						$('#variety').select2({
							placeholder: 'تنوع را انتخاب کنید'
						});
                    }
                });
            });

            let counter = 0;

            $('#variety').on('select2:select', () => {

                const selectedVarietyId = $('#variety').val();
                const selectedVariety = $('#variety option:selected');

                const price = selectedVariety.data('price');
                const formattedPrice = new Intl.NumberFormat('fa-IR').format(price);

                const isDuplicate = $('#NewItemsTable tbody tr').filter((index, row) => {
                    return $(row).find('.variety-id').text() == selectedVarietyId;
                }).length > 0;

                if (isDuplicate) {
                    alert("شما این تنوع را قبلا انتخاب کرده‌اید.");
                    return;
                }
                $('#NewItemsTable tbody').append(`
                    <tr>  
                        <td>${productTitle}</td>  
                        <td>${selectedVariety.text()}</td>  
                        <td>${formattedPrice}</td>  
                        <td>  
                            <input 
								type="number" 
								min="1" 
								class="form-control NewProductInput" 
								data-variety-id="${selectedVarietyId}"
							/>  
                        </td>  
                        <td>  
                            <button type="button" class="delete-btn btn btn-sm btn-icon btn-danger text-white">  
                                <i class="fa fa-minus"></i>  
                            </button>  
                        </td>  
                    </tr>  
                `);
                counter++;
            });

            $('#NewItemsTable').on('click', '.delete-btn', function() {
                $(this).closest('tr').remove();
            });

            $('#Invoicing').click(() => {

                $('.NewProductQuantity').each(function() {
                    let item = orderItems.find((i) => i.variety_id == $(this).data('variety_id'));
                    let newQuantity = $(this).val() - item.quantity;

                    if (newQuantity <= 0) {
                        deleteCarts.push({
                            variety_id: item.variety_id,
                            quantity: newQuantity == 0 ? item.quantity : -newQuantity
                        });
                    } else {
                        addCarts.push({
                            variety_id: item.variety_id,
                            quantity: newQuantity
                        });
                    }
                });

                $('#NewItemsTable').find('tbody tr').each(function() {
                    let newProductInput = $(this).find('td .NewProductInput');
                    addCarts.push({
                        variety_id: newProductInput.data('variety-id'),
                        quantity: parseInt(newProductInput.val())
                    });
                });

                $('#OrderItemsAddCarts').val(JSON.stringify(addCarts));
                $('#OrderItemsDeleteCarts').val(JSON.stringify(deleteCarts));

                $('#UpdateOrderItemsQuantityForm').submit();
            });

        });
    </script>
@endsection
