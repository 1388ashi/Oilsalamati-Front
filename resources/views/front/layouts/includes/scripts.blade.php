<script src="{{ asset('front/assets/js/plugins.js') }}"></script>
<script src="{{ asset('front/assets/js/main.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('front/assets/custom/custom.js') }}"></script>
<script src="{{ asset('front/assets/custom/sweetalert.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>


<script>
function increaseQuantity(button) {  
    const quantityInput = button.previousElementSibling;
    let newVal = parseInt(quantityInput.value) + 1;
    quantityInput.value = newVal;

    updateQuantityInCookie(quantityInput.dataset.key, newVal);
    updateQuantityOnServer(quantityInput.dataset.cartId, newVal, quantityInput);
}  

function decreaseQuantity(button) {  
    const quantityInput = button.nextElementSibling; 
    let newVal = parseInt(quantityInput.value);
    if (newVal > 1) {  
        newVal--;
        quantityInput.value = newVal;

        updateQuantityInCookie(quantityInput.dataset.key, newVal);
        updateQuantityOnServer(quantityInput.dataset.cartId, newVal, quantityInput);
    }  
}

function updateQuantityInCookie(key, newVal) {
    let productData = getCookie('productData');
    productData = productData ? JSON.parse(decodeURIComponent(productData)) : [];

    const productIndex = productData.findIndex(product => product.variety_id === key);
    if (productIndex !== -1) {
        productData[productIndex].variety_quantity = newVal;
    }
    document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;
}

function updateQuantityOnServer(cartId, newVal, input) {
    $.ajax({
        url: `{{ route('cart.index') }}/${cartId}`,
        type: 'PUT',
        data: {
            quantity: newVal,
        },
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: (response) => {  
            console.log(response);
        },  
        error: (xhr, status, error) => {  
            console.log(xhr);
        }  
    });
}

function deleteCartOnServer(cartId) {
    $.ajax({
        url: `{{ route('cart.index') }}/${cartId}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: (response) => {  
            console.log(response);
        },  
        error: (xhr, status, error) => {  
            console.log(xhr);
        }  
    });
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function removeVariety(event, varietyId) {
    Swal.fire ({  
        title: 'حذف محصول',
        text: 'آیا تمایل دارید محصول را از سبد حذف کنید',
        icon: "warning",  
        confirmButtonText: 'حذف کن',  
        showDenyButton: true,  
        denyButtonText: 'انصراف',  
        dangerMode: true,  
    }).then((result) => {  
        if (result.isConfirmed) {  
            const cartId = $(`input[data-cart-id][data-variety-id="${varietyId}"]`).data('cart-id'); // دریافت cartId
            deleteCartOnServer(cartId);
            Swal.fire({  
                icon: "success",  
                text: "آیتم با موفقیت از سبد خرید حذف شد."  
            });  
            event.preventDefault(); 
            let productData = getCookie('productData');
            if (productData) {
                productData = JSON.parse(decodeURIComponent(productData));

                productData = productData.filter(product => product.variety_id !== varietyId);
                
                if (productData.length > 0) {
                    document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/`; 
                } else {
                    document.cookie = 'productData=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;'; // حذف کوکی
                }
                $(`li[data-variety-id="${varietyId}"]`).remove();
            } 
        } 
    });  
}

$(document).ready(function() {
    let productData = getCookie('productData');
    if (productData) {
        productData = JSON.parse(decodeURIComponent(productData));
        console.log(productData);

        let uniqueProducts = {};

        // جمع‌آوری اطلاعات محصولات مشابه
        productData.forEach(function(product) {
            if (uniqueProducts[product.variety_id]) {
                uniqueProducts[product.variety_id].variety_quantity += parseInt(product.variety_quantity);
                uniqueProducts[product.variety_id].variety_price += parseFloat(product.variety_price);
            } else {
                uniqueProducts[product.variety_id] = { ...product };
            }
        });

        let totalItems = Object.keys(uniqueProducts).length;
        $('#cart-count').text(`سبد خرید شما (${totalItems} مورد)`);
        $('#num-cart-count').text(`${totalItems}`);

        let totalPrice = 0; // جمع کل قیمت‌ها
        for (let key in uniqueProducts) {
            let product = uniqueProducts[key];

            let varietyPrice = parseFloat(product.variety_price) * 1000; // تبدیل قیمت به تومان
            let quantity = parseInt(product.variety_quantity); // تعداد
            let productTotalPrice = Math.floor(varietyPrice * quantity); // قیمت کل محصول
            totalPrice += productTotalPrice;

            let productHtml = `
            <li class="item d-flex justify-content-center align-items-center" data-variety-id="${product.variety_id}">
                <a class="product-image rounded-0" href="product-layout1.html">
                    <img
                        class="rounded-0 blur-up lazyload"
                        data-src="${product.product_image}"
                        src="${product.product_image}"
                        alt="product"
                        title="محصول"
                        width="120"
                        height="170"
                    />
                </a>
                <div class="product-details">
                    <a class="product-title" href="product-layout1.html">${product.title_product}</a>
                    <div class="variant-cart my-2">${product.variety_value}</div>
                    <div class="priceRow">
                        <div class="product-price">
                            <span class="price">${product.variety_price}</span>
                        </div>
                    </div>
                </div>
                <div class="qtyDetail text-center">
                    <div class="qtyField">
                        <a class="qtyBtn minus" onclick="decreaseQuantity(this)">  
                            <i style="cursor: pointer" class="icon anm anm-minus-r"></i>  
                        </a>  
                        <input type="text" name="quantity" 
                            value="${product.variety_quantity}" 
                            class="qty" 
                            data-cart-id="" 
                            data-key="${key}" 
                            readonly/>
                        <a class="qtyBtn plus" onclick="increaseQuantity(this)">  
                            <i style="cursor: pointer" class="icon anm anm-plus-r"></i>  
                        </a>
                    </div>
                    <a href="#" class="edit-i remove" onclick="removeVariety(event, '${product.variety_id}')" data-variety-id="${product.variety_id}">
                        <i class="icon anm anm-times-r" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف"></i>
                    </a>
                </div>
            </li>`;

            // اضافه کردن محصول به لیست
            $('#output').append(productHtml);
        }
        console.log("Total Price before formatting: ", totalPrice);
        let totalPriceFormatted = formatPrice(totalPrice);
        $('#cart-price').text(totalPriceFormatted);
    }
});

function formatPrice(price) {  
    let millionPart = Math.floor(price / 1000000);
    let thousandPart = Math.floor((price % 1000000) / 1000);
    let result = '';

    if (millionPart > 0) {
        result += millionPart + ' میلیون تومان';
    }
    if (thousandPart > 0) {
        if (result) result += ' و ';  // برای جدا کردن بخش‌ها
        result += thousandPart + ' هزار تومان';
    }
    
    return result || (price + ' تومان');
}

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
