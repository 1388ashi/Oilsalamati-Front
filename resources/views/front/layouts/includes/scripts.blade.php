<script src="{{ asset('front/assets/js/plugins.js') }}"></script>
<script src="{{ asset('front/assets/js/main.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>

<script>
// function getCookie(name) {  
//     var nameEQ = name + "=";  
//     var ca = document.cookie.split(';');  
//     for (var i = 0; i < ca.length; i++) {  
//         var c = ca[i];  
//         while (c.charAt(0) === ' ') c = c.substring(1, c.length);  
//         if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));  
//     }  
//     return null;  
// }  
// function deleteCookie(name) {
//     document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
// }
// function increaseQuantity(button) {  
//     const quantityInput = button.previousElementSibling;
//     quantityInput.value = parseInt(quantityInput.value) + 1;
// }  
// function decreaseQuantity(button) {  
//     const quantityInput = button.nextElementSibling; 
//     if (parseInt(quantityInput.value) > 1) {  
//         quantityInput.value = parseInt(quantityInput.value) - 1;
//     }  
// }  
// $(document).ready(function() {  
//     var productDataString = getCookie('productData');  
//     if (productDataString) {  
//         var productData = JSON.parse(productDataString);  

//         var listContainer = $('<ul></ul>');  

//         console.log(productDataString);
//         if (!$.isEmptyObject(productData.varieties)) { 
//             console.log(productData);
//             let aggregatedVarieties = {};  

//             $.each(productData.varieties, function(key, value) {  
//                 if (aggregatedVarieties[key]) {  
//                     aggregatedVarieties[key].quantity += 1;   
//                 } else {  
//                     aggregatedVarieties[key] = {  
//                         price: value.price,  
//                         quantity: 1
//                     };  
//                 }  
//             });  
//             let totalQuantity = 0;  
//             let totalPrice = 0;  
//             if (!$.isEmptyObject(productData.varieties)) {  
//                 $.each(productData.varieties, function(key, variety) {  
//                     totalQuantity += variety.quantity || 1; 
//                     totalPrice += variety.price || 10000; 
//                 });  
//             }  
//             $('#cart-count').text(`سبد خرید شما (${totalQuantity} مورد)`);
//             $('#cart-price').text(`${totalPrice.toLocaleString()} تومان`);  
            
//             $.each(aggregatedVarieties, function(key, variety) {  
//                 console.log(variety);
//                 totalQuantity += variety.quantity || 1; 
//                 var varietyItem = $('<li class="item d-flex justify-content-center align-items-center"></li>');  
//                 varietyItem.append(`  
//                     <a class="product-image rounded-0" href="product-layout1.html">  
//                         <img class="rounded-0 blur-up lazyload" src="${productData.mainImage}" alt="product" title="محصول" width="120" height="170" />  
//                     </a>  
//                     <div class="product-details">  
//                         <a class="product-title" href="product-layout1.html">${variety.title}</a>  
//                         <div class="variant-cart my-2">${key}</div>  
//                         <div class="priceRow">  
//                             <div class="product-price">  
//                                         <span class="price">${variety.price.toLocaleString()} تومان</span>  
//                             </div>  
//                         </div>  
//                     </div>  
//                     <div class="qtyDetail text-center">  
//                         <div class="qtyField">  
//                             <a class="qtyBtn minus" onclick="decreaseQuantity(this)">  
//                                 <i style="cursor: pointer" class="icon anm anm-minus-r"></i>  
//                             </a>  
//                             <input type="text" name="quantity" value="${totalQuantity}" min="1" class="qty" readonly data-key="${key}" />  
//                             <a class="qtyBtn plus" onclick="increaseQuantity(this)">  
//                                 <i style="cursor: pointer" class="icon anm anm-plus-r"></i>  
//                             </a>  
//                         </div>  
//                         <a href="#" class="remove" data-name="productData_${key}"><i class="icon anm anm-times-r" title="حذف"></i></a>  
//                     </div>  
//                 `);  
//                 listContainer.append(varietyItem);  
//             });
//         } else {  
//             listContainer.append('<li>هیچ محصولی انتخاب نشده است.</li>');  
//         }
//         $('#output').append(listContainer);  
//     } else {  
//         $('#output').append('<p>هیچ داده‌ای در کوکی موجود نیست.</p>');  
//     }  

//     $(document).on('click', '.remove', function(e) {
//         e.preventDefault();
//         var cookieName = $(this).data('name');
//         deleteCookie(cookieName);
//         $(this).closest('li').remove();
//         alert("آیتم با موفقیت حذف شد.");
//     });
// });

    let typingTimer;

    $('#SearchProductInput').on('input', function() {
        clearTimeout(typingTimer);
        const searchInput = $(this).val();

        typingTimer = setTimeout(function() {
            searchProducts(searchInput);
        }, 1000);
    });

    function searchProducts(value) {
        $.ajax({
            url: '{{ route("products.search") }}',
            type: 'GET',
            data: {
                q: value
            },
            success: function(response) {

                if (response.data.products.length == 0) {
                    $("#ProductSearchBox").append('<li class="item vala w-100 text-center text-muted">شما هیچ موردی در جستجوی خود ندارید.</li>'); 
                    return;
                }

                const products = response.data.products;
                const ProductSearchItem = $('#ProductSearchItem');

                $('#ProductSearchBox').empty();

                products.forEach(product => {

                    let item = ProductSearchItem.clone();

                    let img = item.find('#ProductSearchImage');
                    let title = item.find('#ProductSearchTitle');
                    let oldPrice = item.find('#ProductSearchOldPrice');
                    let price = item.find('#ProductSearchPrice');
                    let link = item.find('a')

                    item.removeAttr('id');  
                    item.removeClass('d-none');
                    link.attr('href', '{{ route("products.show", "") }}' + '/' + product.id);  

                    img.attr({
                        dataSrc: product.images_showcase.main_image.url,
                        src: product.images_showcase.main_image.url,
                        alt: product.title,
                        title: product.title
                    });

                    title.text(product.title);
                    price.text(product.final_price.base_amount.toLocaleString() + ' ' + 'تومان');

                    img.removeAttr('id');
                    title.removeAttr('id');
                    price.removeAttr('id');

                    if (product.final_price.discount > 0) {
                        oldPrice.text(product.final_price.amount.toLocaleString() + ' ' + 'تومان');
                        oldPrice.removeAttr('id');
                    }else {
                        oldPrice.remove();
                    }

                    $("#ProductSearchBox").append(item);  

                });

            }
        });
    }
</script>

@if (session()->has('success'))
    <script>
        $(document).ready(function() {
            $.growl.notice({
                title: "موفق شد!",
                message: "{{ session('success') }}"
            });
        });
    </script>
@elseif(session()->has('error'))
    <script>
        $(document).ready(function() {
            $.growl.error({
                title: "خطایی رخ داده!",
                message: "{{ session('error') }}"
            });
        });
    </script>
@elseif(session()->has('warning'))
    <script>
        $(document).ready(function() {
            $.growl.warning({
                title: "هشدار!",
                message: "{{ session('warning') }}"
            });
        });
    </script>
@elseif(session()->has('info'))
    <script>
        $(document).ready(function() {
            $.growl.warning({
                title: "هشدار!",
                message: "{{ session('warning') }}"
            });
        });
    </script>
@endif
