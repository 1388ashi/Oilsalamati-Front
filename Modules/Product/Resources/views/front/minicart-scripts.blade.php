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

                let productData = getCookie('productData');
                if (productData) {
                    productData = JSON.parse(decodeURIComponent(productData));

                    productData = productData.filter(product => product.variety_id !== String(
                        variety_id));
                    document.cookie =
                        `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;

                    updateCartDisplay();
                }
            });
            updateCartDisplay();
        });

        function updateCartDisplay() {  
            let productData = getCookie('productData');  

            if (!productData) {  
                productData = null;
            } else {  
                productData = JSON.parse(decodeURIComponent(productData));  
            }  

            $('#output').empty();  
            console.log(productData);  

            if (productData && productData.length > 0) {  
                $('.minicart-bottom').show();  
                let totalItems = productData.length;  
                $('#cart-count').text(`سبد خرید شما (${totalItems} مورد)`);  
                $('#num-cart-count').text(`${totalItems}`);  

                $('#output').empty();  
                let totalPrice = 0;  

                productData.forEach(function(product) {  
                    let quantity = parseInt(product.variety_quantity);  
                    let productTotalPrice = Math.floor(parseFloat(product.variety_price) * 1000 * quantity);  
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
                                        data-key="${product.variety_id}"   
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

                    $('#output').append(productHtml);  
                });  

                let totalPriceFormatted = formatPrice(totalPrice);  
                $('#cart-price').text(totalPriceFormatted);  
            } else {  
                $('#output').append('<li>سبد خرید شما خالی است.</li>');  
                $('#cart-count').text('سبد خرید شما (0 مورد)');  
                $('.minicart-bottom').hide();  
            }  
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

        function updateQuantityInCookie(key, newVal) {
            let productData = getCookie('productData');
            productData = productData ? JSON.parse(decodeURIComponent(productData)) : [];

            const productIndex = productData.findIndex(product => product.variety_id === key);
            if (productIndex !== -1) {
                productData[productIndex].variety_quantity = newVal;
            }
            document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;
        }

        function removeVariety(event, variety_id) {
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
                        productData = productData.filter(product => product.variety_id !== String(variety_id));
                        document.cookie =
                            `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;

                        Swal.fire({
                            icon: "success",
                            text: "محصول با موفقیت از سبد خرید حذف شد."
                        });

                        updateCartDisplay(); // به‌روزرسانی سبد خرید  
                    }
                }
            });
        }

        function increaseQuantity(button) {
            const quantityInput = button.previousElementSibling;
            let newVal = parseInt(quantityInput.value) + 1;
            let variety_id = quantityInput.dataset.key;
            quantityInput.value = newVal;
            updateQuantityInCookie(variety_id, newVal);
            updateTotalPrice(newVal, variety_id);
            Swal.fire({  
                icon: "success",  
                text: "تعداد محصول با موفقیت افزایش یافت."
            });  
        }

        function decreaseQuantity(button) {
            const quantityInput = button.nextElementSibling;
            let newVal = parseInt(quantityInput.value);
            let variety_id = quantityInput.dataset.key;
            if (newVal > 1) {
                newVal--;
                quantityInput.value = newVal;
                updateQuantityInCookie(variety_id, newVal);
                updateTotalPrice(newVal, variety_id);
                Swal.fire({  
                    icon: "success",  
                    text: "تعداد محصول با موفقیت کاهش یافت."
                });  
            }
        }

        function updateTotalPrice(newVal, inputVarietyId) {
            let totalPrice = 0;
            let totalItems = 0;
            let productData = getCookie('productData');

            if (productData) {
                productData = JSON.parse(decodeURIComponent(productData));
            }

            $('.qtyField').each(function() {
                let quantity = parseInt($(this).find('.qty').val());
                let varietyId = $(this).find('.qty').data('key');

                let product = productData.find(product => product.variety_id === String(varietyId));

                if (product) {
                    console.log(product.variety_price);
                    let price = parseFloat(product.variety_price);
                    totalPrice += Math.floor(price * quantity);
                }
                if (quantity === 0) {
                    productData = productData.filter(product => product.variety_id !== String(varietyId));
                }
            });

            document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;
            totalPrice += '000';
            totalPrice = parseInt(totalPrice);
            totalPrice = totalPrice.toLocaleString();

            $('#cart-price').text(totalPrice + ' تومان');
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
