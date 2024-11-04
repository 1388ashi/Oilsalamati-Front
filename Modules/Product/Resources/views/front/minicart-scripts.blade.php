<script>
    $(document).ready(function() {
        updateCartDisplay();  

    });  

    function updateCartDisplay() {  
        let productData = getCookie('productData');  
        if (productData) {  
            productData = JSON.parse(decodeURIComponent(productData));  
            let totalItems = productData.length;  
            $('#cart-count').text(`سبد خرید شما (${totalItems} مورد)`);  
            $('#num-cart-count').text(`${totalItems}`);  

            $('#output').empty();
            let totalPrice = 0; 

            productData.forEach(function(product) {  
                let varietyPrice = parseFloat(product.variety_price) * 1000;
                let quantity = parseInt(product.variety_quantity); // تعداد  
                let productTotalPrice = Math.floor(varietyPrice * quantity); 
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
    function removeVariety(event, variety_id) {  
        Swal.fire ({  
            text: 'آیا تمایل دارید محصول را از لیست علاقه مندی ها حذف کنید',
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
                    productData = productData.filter(product => product.variety_id !== variety_id);
                    document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  
                    
                    updateCartDisplay();
                }  
            }  
        }); 
    }  
    function formatPrice(price) {  
        let millionPart = Math.floor(price / 1000000);
        let thousandPart = Math.floor((price % 1000000) / 1000);
        let result = '';

        if (millionPart > 0) {
            result += millionPart + ' میلیون تومان';
        }
        if (thousandPart > 0) {
            if (result) result += ' و ';
            result += thousandPart + ' هزار تومان';
        }
        
        return result || (price + ' تومان');
    }
</script>
