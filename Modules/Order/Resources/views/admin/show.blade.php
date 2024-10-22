@extends('admin.layouts.master')
@section('styles')
    <link href="{{ asset('assets/plugins/accordion/accordion.css') }}" rel="stylesheet" />
@endsection
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست سفارشات', 'route_link' => 'admin.orders.index'], ['title' => 'جزئیات سفارش']]" />
        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-warning text-dark font-weight-bold btn-sm">ویرایش سفارش</a>    
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-0">
                    <button id="OrderDetails" class="btn btn-outline-success mx-1">جزئیات سفارش <i
                            class="fa fa-angle-down"></i></button>
                    <button id="CustomerDetails" class="btn btn-outline-dark mx-1">اطلاعات مشتری <i
                            class="fa fa-angle-down"></i></button>
                    <button id="ReceiverDetails" class="btn btn-outline-primary mx-1">اطلاعات دریافت کننده <i
                            class="fa fa-angle-down"></i></button>
                    {{-- <button class="btn btn-outline-warning mx-1" data-target="#EditOrderModal" data-toggle="modal">ویرایش
                        سفارش <i class="fa fa-pencil"></i></button>
                    <button class="btn btn-outline-orange mx-1" data-target="#AddItemToOrderModal"
                        data-toggle="modal">افزودن آیتم <i class="fa fa-plus"></i></button> --}}
                </div>
                <x-alert-danger/>
                <div class="card-body">
                    @include('order::admin.includes.order-details')
                    @include('order::admin.includes.order-items')
                    @include('order::admin.includes.payment-details')
                    @include('order::admin.includes.invoice-details')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // $('#shipping_id').select2({
        //     placeholder: 'حمل و نقل جدید را انتخاب کنید'
        // });
        // $('#address_id').select2({
        //     placeholder: 'آدرس جدید را انتخاب کنید'
        // });
        $('#order_status').select2({
            placeholder: 'وضعیت جدید را انتخاب کنید'
        });
        // $('#products').select2({
        //     placeholder: 'محصول را جستجو کنید'
        // });
        // $('#variety').select2({
        //     placeholder: 'ابتدا محصول را انتخاب کنید'
        // });
        // $('.pay_type').select2({
        //     placeholder: 'روش پرداخت را انتخاب کنید'
        // });
    </script>

    <script>
        // $('.search-product-ajax').select2({
        //     ajax: {
        //         url: '{{ route('admin.products.search') }}',
        //         dataType: 'json',
        //         processResults: (response) => {
        //             let products = response.data.products || [];

        //             return {
        //                 results: products.map(product => ({
        //                     id: product.id,
        //                     title: product.title,
        //                 })),
        //             };
        //         },
        //         cache: true,
        //     },
        //     placeholder: 'محصول را جستجو کنید',
        //     templateResult: (repo) => {
        //         if (repo.loading) {
        //             return "در حال بارگذاری...";
        //         }

        //         let $container = $(
        //             "<div class='select2-result-repository clearfix'>" +
        //             "<div class='select2-result-repository__meta'>" +
        //             "<div class='select2-result-repository__title'></div>" +
        //             "</div>" +
        //             "</div>"
        //         );

        //         let text = repo.title;
        //         $container.find(".select2-result-repository__title").text(text);

        //         return $container;
        //     },
        //     minimumInputLength: 1,
        //     templateSelection: (repo) => {
        //         return repo.id ? repo.title : repo.text;
        //     }
        // });

        $(document).ready(() => {

            $('#OrderDetails').click(() => $("#order-details").addClass('my-5').slideToggle());
            $('#CustomerDetails').click(() => $("#customer-details").addClass('my-5').slideToggle());
            $('#ReceiverDetails').click(() => $("#receiver-details").addClass('my-5').slideToggle());

            // let orderItems = @json($order->items);

            // ---------------------- Order-Items scripts
            // $('.decrease-quantity').on('click', function() {
            //     const input = $(this).closest('tr').find('.quantity-input');
            //     let currentValue = parseInt(input.val());
            //     if (currentValue > 0) {
            //         input.val(currentValue - 1);
            //     }
            // });

            // $('.increase-quantity').on('click', function() {
            //     const input = $(this).closest('tr').find('.quantity-input');
            //     let currentValue = parseInt(input.val());
            //     input.val(currentValue + 1);
            // });

            // ---------------------- End Order-Items scripts

            // let productTitle = '';
            // $('#products').on('select2:select', (e) => {
            //     const selectedProduct = e.params.data;
            //     productTitle = selectedProduct.title;
            //     let productId = selectedProduct.id;
            //     $.ajax({
            //         url: '{{ route('admin.stores.load-varieties') }}',
            //         type: 'POST',
            //         data: {
            //             product_id: productId
            //         },
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            //         success: function(response) {

            //             let varieties = response.varieties;
            //             let options = '';

            //             varieties.forEach((variety) => {

            //                 let id = variety.id;
            //                 let balance = variety.store.balance ?? 0;
            //                 let price = variety.final_price.amount ?? 0;
            //                 let attr = null;

            //                 if (variety.attributes[0] != undefined) {
            //                     attr = variety.attributes[0].pivot.value;
            //                 }

            //                 if (variety.color != null) {
            //                     attr = variety.color.name;
            //                 }

            //                 let optionTitle = `شناسه: ${id}`;

            //                 if (attr) {
            //                     optionTitle += ` | ${attr}`;
            //                 }

            //                 optionTitle += ` | موجودی: ${balance}`;

            //                 let optionValue = variety.id;

            //                 options +=
            //                     `<option value="${optionValue}" data-balance="${balance}" data-price="${price}">${optionTitle}</option>`

            //             });

            //             $('#variety').html(options);
            //         }
            //     });
            // });

            // $('#products-discount-section').hide();
            // let counter = 0;

            // $('#variety').on('select2:select', () => {

            //     const selectedVarietyId = $('#variety').val();
            //     const selectedVariety = $('#variety option:selected');

            //     const price = selectedVariety.data('price');
            //     const formattedPrice = new Intl.NumberFormat('fa-IR').format(price);

            //     const isDuplicate = $('#products-discount-table tbody tr').filter((index, row) => {
            //         return $(row).find('.variety-id').text() == selectedVarietyId;
            //     }).length > 0;

            //     if (isDuplicate) {
            //         alert("شما این تنوع را قبلا انتخاب کرده‌اید.");
            //         return;
            //     }
            //     $('#products-discount-section').show();
            //     $('#products-discount-table tbody').append(`
            //         <tr role="row">  
            //             <td role="cell" aria-colindex="1" class="product-title">${productTitle}</td>  
            //             <td role="cell" aria-colindex="2" class="variety-title">${selectedVariety.text()}</td>  
            //             <td role="cell" aria-colindex="3" class="variety-price">${formattedPrice}</td>  
            //             <td role="cell" aria-colindex="5">  
            //                 <input type="number" name="addCarts[${counter}][quantity]" min="1" class="form-control quantity-input">  
            //                 <input type="hidden" name="addCarts[${counter}][variety_id]" value="${selectedVarietyId}">  
            //             </td>  
            //             <td role="cell" aria-colindex="8">  
            //                 <button type="button" class="delete-btn btn btn-sm btn-icon btn-danger text-white">  
            //                     <i class="fa fa-minus"></i>  
            //                 </button>  
            //             </td>  
            //         </tr>  
            //     `);
            //     counter++;
            // });

            // $('#products-discount-table').on('click', '.delete-btn', function() {
            //     $(this).closest('tr').remove();
            // });

            // $('#Invoicing').click(() => {
            //     let addCarts = [];
            //     let deleteCarts = [];

            //     $('.NewProductQuantity').each(function() {
            //         let item = orderItems.find((i) => i.variety_id == $(this).data('variety_id'));
            //         let newQuantity = $(this).val() - item.quantity;

            //         if (newQuantity <= 0) {
            //             deleteCarts.push({
            //                 variety_id: item.variety_id,
            //                 quantity: newQuantity == 0 ? item.quantity : -newQuantity
            //             });
            //         } else {
            //             addCarts.push({
            //                 variety_id: item.variety_id,
            //                 quantity: newQuantity
            //             });
            //         }
            //     });

            //     $('#OrderItemsAddCarts').val(JSON.stringify(addCarts));
            //     $('#OrderItemsDeleteCarts').val(JSON.stringify(deleteCarts));

            //     $('#UpdateOrderItemsQuantityForm').submit();
            // });

        });
    </script>
@endsection

