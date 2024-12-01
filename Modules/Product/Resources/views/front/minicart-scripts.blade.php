<script>
    @if (auth()->guard('customer')->user())
        let carts = @json(auth()->guard('customer')->user()->carts()->with(['variety.product', 'variety.attributes'])->get()->map(function ($cart) {
            $cart->variety->product->setAppends(['images_showcase']);
            return $cart;
        }));
        function updateCartInfo(data) {
            if (data) {
                carts = data;
            }
            let totalItems = carts.length;
            $('#cart-count').text(`سبد خرید شما (${totalItems} مورد)`);
            $('#num-cart-count').text(`${totalItems}`);

            $('#output').empty();
            let totalPrice = 0;
            console.log(totalItems);
            if (totalItems == 0) {
                $('#output').append('<li>سبد خرید شما خالی است.</li>');
                $('.minicart-bottom').hide();  
                return
            }
            $('.minicart-bottom').show();  
            carts.forEach(function(cart) {
                let varietyPrice = parseFloat(cart.price);
                let quantity = parseInt(cart.quantity);
                let productTotalPrice = Math.floor(varietyPrice * quantity);
                totalPrice += productTotalPrice;
                let title = cart.variety.product?.title ?? cart.variety.title;
                let attributesValue = cart.variety?.attributes?.[0]?.pivot?.value ?? cart.variety.title;
                console.log(cart);
                let imageValue = cart.variety.main_image_showcase?.url ?? cart.variety.product.images_showcase
                    .main_image.url;
                // let imageValue = '';

                let productHtml = `  
                <li class="item d-flex justify-content-center align-items-center" data-variety-id="${cart.variety_id}">  
                    <a class="product-image rounded-0" href="product-layout1.html">  
                        <img  
                            class="rounded-0 blur-up lazyload"  
                            data-src="${imageValue}"  
                            src="${imageValue}"  
                            alt="product"  
                            title="محصول"  
                            width="120"  
                            height="170"  
                        />  
                    </a>  
                    <div class="product-details">  
                        <a class="product-title" href="product-layout1.html">${title}</a>  
                        <div class="variant-cart my-2">${attributesValue}</div>  
                        <div class="priceRow">  
                            <div class="product-price">  
                                <span class="price">${cart.price.toLocaleString()} تومان</span>  
                            </div>  
                        </div>  
                    </div>  
                    <div class="qtyDetail text-center">  
                        <div class="qtyField">  
                            <a class="qtyBtn minus" onclick="decreaseQuantity(${cart.variety_id})">  
                                <i style="cursor: pointer" class="icon anm anm-minus-r"></i>  
                            </a>  
                            <input type="text" name="quantity"   
                                value="${cart.quantity}"   
                                class="qty"   
                                data-key="${cart.variety_id}"   
                                readonly/>  
                            <a class="qtyBtn plus" onclick="increaseQuantity(${cart.variety_id})">  
                                <i style="cursor: pointer" class="icon anm anm-plus-r"></i>  
                            </a>  
                        </div>  
                        <a href="#" class="edit-i remove" onclick="removeVariety(event, '${cart.id}')" data-variety-id="${cart}">  
                            <i class="icon anm anm-times-r" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف"></i>  
                        </a>  
                    </div>  
                </li>`;

                $('#output').append(productHtml);
            });

            let totalPriceFormatted = (totalPrice.toLocaleString()) + ' ' + 'تومان';
            $('#cart-price').text(
                totalPriceFormatted);
        }

        function updateTotalPrice() {
            let totalPrice = 0;
            let totalItems = 0;

            $('.qtyField').each(function() {
                let quantity = parseInt($(this).find('.qty').val());
                let variety_id = $(this).find('.qty').data('key');

                let cartItem = carts.find(cart => cart.variety_id == variety_id);

                if (cartItem) {
                    let price = parseFloat(cartItem.price);
                    totalPrice += Math.floor(price * quantity);
                }
            });

            $('#cart-price').text(totalPrice.toLocaleString() + ' تومان');
        }

        function increaseQuantity(varietyId) {
            const quantityInput = $(`.qtyField [data-key="${varietyId}"]`);
            let newVal = parseInt(quantityInput.val()) + 1;
            quantityInput.val(newVal);
            updateTotalPrice();
            let cart = carts.find(cart => cart.variety_id == varietyId);
            updateQuantityOnServer(newVal, cart);
        }

        function removeVariety(event, cart) {
            event.preventDefault();

            Swal.fire({
                text: 'آیا تمایل دارید محصول را از سبد خرید حذف کنید؟',
                icon: "warning",
                confirmButtonText: 'حذف کن',
                showDenyButton: true,
                denyButtonText: 'انصراف',
                dangerMode: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteCartOnServer(cart);
                }
            });
        }

        function deleteCartOnServer(cart) {
            return $.ajax({
                url: `{{ route('cart.remove') }}/${cart}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: (response) => {
                    updateCartInfo(response.data.carts);
                    Swal.fire({
                        icon: "success",
                        text: response.message
                    });
                },
                error: (xhr, status, error) => {
                    Swal.fire({
                        icon: "error",
                        text: error.responseJSON.message
                    });
                }
            });
        }

        function decreaseQuantity(varietyId) {
            const quantityInput = $(`.qtyField [data-key="${varietyId}"]`);
            let newVal = parseInt(quantityInput.val());
            if (newVal > 1) {
                newVal--;
                quantityInput.val(newVal);
                updateTotalPrice();
                let cart = carts.find(cart => cart.variety_id == varietyId);
                updateQuantityOnServer(newVal, cart);
            }
        }

        function updateQuantityOnServer(newVal, cart) {
            $.ajax({
                url: `{{ route('cart.update') }}/${cart.id}`,
                type: 'PUT',
                data: {
                    quantity: newVal,
                },
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: (response) => {
                    Swal.fire({
                        icon: "success",
                        text: response.message
                    });
                },
                error: (error) => {
                    Swal.fire({
                        icon: "error",
                        text: error.responseJSON.message
                    });
                }
            });
        }
        $(document).ready(function() {
            updateCartInfo();
        });
    @else
        $(document).ready(function() {  
            $('.cart-remove').on('click', function(event) {  
                event.preventDefault();  
                const variety_id = $(this).data('variety');  
                const variety_value = $(this).data('value');

                let productData = getCookie('productData');  
                if (productData) {  
                    productData = JSON.parse(decodeURIComponent(productData));  

                    productData = productData.filter(product =>   
                        !(product.variety_id === String(variety_id) && product.variety_value === variety_value)  
                    );  
                    document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  

                    updateCartDisplay();  
                }  
            });  
            updateCartDisplay();  
        });  

        function updateCartDisplay() {  
            let productData = getCookie('productData');  

            if (!productData) {  
                productData = [];  
            } else {  
                productData = JSON.parse(decodeURIComponent(productData));  
            }  

            $('#output').empty();  
            console.log(productData);  

            if (productData.length > 0) {  
                $('.minicart-bottom').show();  

                let totalPrice = 0;  
                let totalItems = 0;   
                let productsByVarietyValue = {};   

                productData.forEach(function(product) {  
                    let quantity = parseInt(product.variety_quantity);  
                    let varietyPrice = product.variety_price;  
                    
                    if (typeof varietyPrice === 'string') {  
                        varietyPrice = varietyPrice.replace(/,/g, '');   
                    } else if (typeof varietyPrice === 'number') {  
                        varietyPrice = varietyPrice.toString();   
                    } else {  
                        console.error('variety_price has an unexpected type:', varietyPrice);  
                        return;  
                    }  
                    
                    let productTotalPrice = Math.floor(parseFloat(varietyPrice) * quantity);  
                    totalPrice += productTotalPrice;  

                    totalItems += quantity;   

                    let key = product.variety_value;  

                    if (!productsByVarietyValue[key]) {  
                        productsByVarietyValue[key] = { ...product };  
                    } else {  
                        productsByVarietyValue[key].variety_quantity += quantity;  
                        removeFromCookie(product.variety_id);
                    }  
                });  

                let i = 0;  
                for (let key in productsByVarietyValue) {  
                    
                    let product = productsByVarietyValue[key];  
                    i += 1;  
                    
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
                                        data-key="${product.variety_value}"   
                                        readonly/>  
                                    <a class="qtyBtn plus" onclick="increaseQuantity(this)">  
                                        <i style="cursor: pointer" class="icon anm anm-plus-r"></i>  
                                    </a>  
                                </div>  
                                <a href="#" class="edit-i remove" onclick="removeVariety(event, '${product.variety_id}', '${product.variety_value}')" data-variety-id="${product.variety_id}" data-value="${product.variety_value}">  
                                    <i class="icon anm anm-times-r" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف"></i>  
                                </a>  
                            </div>  
                        </li>`;  

                    $('#output').append(productHtml);  
                }  

                $('#cart-count').text(`سبد خرید شما (${i} مورد)`);  
                $('#num-cart-count').text(`${i}`);  
                $('#cart-price').text(totalPrice.toLocaleString() + ' تومان');  

            } else {  
                $('#output').append('<li>سبد خرید شما خالی است.</li>');  
                $('#cart-count').text('سبد خرید شما (0 مورد)');  
                $('#num-cart-count').text(`0`);  
                $('.minicart-bottom').hide();  
            }  
        }  

        function setCookie(name, value, days) {  
            const d = new Date();  
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();  
            document.cookie = name + "=" + (value || "") + ";" + expires + ";path=/";  
        }  
        function addProductToData(product) {  
            let productData = getCookie('productData') ? JSON.parse(decodeURIComponent(getCookie('productData'))) : [];  

            let productImage = product.product_image ? product.product_image : 'path/to/default_image.png';  

            let newProduct = {  
                title_product: product.title_product,  
                variety_id: product.variety_id,  
                variety_price: product.variety_price,  
                variety_quantity: product.variety_quantity,  
                variety_value: product.variety_value,  
                product_image: productImage 
            };  

            productData.push(newProduct);  
            setCookie('productData', JSON.stringify(productData), 18);
        }
        function getCookie(name) {  
            let cookieArr = document.cookie.split(";");  

            for (let i = 0; i < cookieArr.length; i++) {  
                let cookiePair = cookieArr[i].split("=");  
                if (name == cookiePair[0].trim()) {  
                    return decodeURIComponent(cookiePair[1]);  
                }  
            }  

            return null;  
        }  
        function removeFromCookie(varietyId) {  
            let productData = getCookie('productData');  
            if (productData) {  
                productData = JSON.parse(decodeURIComponent(productData));  
                productData = productData.filter(product => product.variety_id !== varietyId);  
                setCookie('productData', JSON.stringify(productData), 7); 
            }  
        }   
        function removeVariety(event, variety_id, variety_value) {  
            Swal.fire({  
                text: 'آیا تمایل دارید محصول را از سبد خرید حذف کنید؟',  
                icon: "warning",  
                confirmButtonText: 'حذف کن',  
                showDenyButton: true,  
                denyButtonText: 'انصراف',  
                dangerMode: true,  
            }).then((result) => {  
                if (result.isConfirmed) {  
                    event.preventDefault();  
                    let productData = getCookie('productData');  

                    if (productData) {  
                        productData = JSON.parse(decodeURIComponent(productData));
                        productData = productData.filter(product =>   
                            !(product.variety_value === variety_value)  
                        );  
                        document.cookie =  
                            `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  

                        Swal.fire({  
                            icon: "success",  
                            text: "محصول با موفقیت از سبد خرید حذف شد."  
                        });  

                        updateCartDisplay();
                    }  
                }  
            });  
        }   

        function updateQuantityInCookie(variety_value, newVal) {  
    let productData = getCookie('productData');  
    productData = productData ? JSON.parse(decodeURIComponent(productData)) : [];  

    const productIndex = productData.findIndex(product => String(product.variety_value) === variety_value);  
    if (productIndex !== -1) {  
        productData[productIndex].variety_quantity = newVal;  

        // اگر کمیت صفر شود، محصول را حذف کنیم  
        if (newVal <= 0) {  
            productData.splice(productIndex, 1); // حذف محصول  
        }  
    } else if (newVal > 0) {  
        // اگر محصولی جدید باشد، آن را اضافه کنیم  
        // کد شما برای اضافه کردن محصول جدید به آرایه باید در اینجا قرار گیرد  
    }  

    // به‌روزرسانی کوکی  
    document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  
}  

function increaseQuantity(button) {  
    const quantityInput = button.previousElementSibling;  
    let newVal = parseInt(quantityInput.value) + 1;  
    let variety_value = quantityInput.dataset.key;  
    quantityInput.value = newVal;  
    updateQuantityInCookie(variety_value, newVal);  
    updateTotalPrice();  
    
    Swal.fire({  
        icon: "success",  
        text: "تعداد محصول با موفقیت افزایش یافت."  
    });  
}  

function decreaseQuantity(button) {  
    const quantityInput = button.nextElementSibling;  
    let newVal = parseInt(quantityInput.value) - 1; // کاهش مقدار یک واحد  
    let variety_value = quantityInput.dataset.key;  

    if (newVal >= 0) { // فقط اگر کمیت >= 0 باشد  
        quantityInput.value = newVal;  
        updateQuantityInCookie(variety_value, newVal);  
        updateTotalPrice();  

        if (newVal === 0) {  
            Swal.fire({  
                icon: "warning",  
                text: "تعداد محصول صفر شده و از سبد خرید حذف شد."  
            });  
        } else {  
            Swal.fire({  
                icon: "success",  
                text: "تعداد محصول با موفقیت کاهش یافت."  
            });  
        }  
    }  
}  

function updateTotalPrice() {  
    let totalPrice = 0;  
    let productData = getCookie('productData');  

    if (productData) {  
        productData = JSON.parse(decodeURIComponent(productData));  
        productData.forEach(product => {  
            let quantity = parseInt(product.variety_quantity) || 0; // در نظر گرفتن مقدار صفر  
            let price = parseFloat(product.variety_price.replace(/,/g, '')) || 0; // در نظر گرفتن قیمت صفر  
            totalPrice += quantity * price; // قیمت کل محاسبه می‌شود  
        });  
    }  

    $('#cart-price').text(totalPrice.toLocaleString() + ' تومان');  
}
    @endif
    $('#proceed-to-checkout').on('click', function() {
        let isLoggedIn = @json(auth()->guard('customer')->user());

        if (isLoggedIn !== null) {
            window.location.href = `{{ route('cart.index') }}`;
        } else {
            window.location.href = `{{ route('pageRegisterLogin') }}`;
        }
    });
</script>
