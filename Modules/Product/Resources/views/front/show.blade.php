@extends('front.layouts.master')
@section('body_class') template-product product-layout1 @endsection
@section('styles')
<style>
.modal-content {  
    border-radius: 8px; 
    padding: 20px;
}  
.modal-header {  
    border-bottom: none;   
}  
.modal-body {  
    padding: 20px;
}  
.btn-auth {  
    background-color: #007bff;
    border-color: #007bff;
}  
.mt-3 {  
    margin-top: 15px; 
}  
</style>
@endsection
@section('content')
<div class="page-header mt-0 py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="breadcrumbs">
                    <a href="/" title="Back to the home page">صفحه اصلی</a>
                    <a href="{{route('products.index')}}" title="Back to the home page">
                        <i class="icon anm anm-angle-left-l"></i>
                        محصولات</a>
                    <span class="main-title fw-bold">
                        <i class="icon anm anm-angle-left-l"></i>
                        جزئیات محصول
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="product-single">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12 col-12 product-layout-img mb-4 mb-md-0">
                <div class="product-details-img product-horizontal-style">
                    <div class="zoompro-wrap">
                        <div class="zoompro-span">
                        <img
                            id="zoompro"
                            class="zoompro"
                            src="{{$product->varieties[0]->images_showcase[0]}}"
                            data-zoom-image="{{$product->varieties[0]->images_showcase[0]}}"
                            alt="محصول"
                            width="625"
                            height="808"
                        />
                        </div>
                        <!-- End Product Image -->
                        <!-- Product Label -->
                        @if ($product->status == 'available')
                        <div class="product-labels">
                            <span class="lbl on-sale">فروش</span>
                        </div>
                        @endif
                        <!-- End Product Label -->
                        <!-- Product Buttons -->
                        <div class="product-buttons">
                        <a
                            href="#;"
                            class="btn btn-primary prlightbox"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="زوم کردن"
                            ><i class="icon anm anm-expand-l-arrows"></i
                        ></a>
                        </div>
                        <!-- End Product Buttons -->
                    </div>
                    <!-- End Product Main -->

                    <!-- Product Thumb -->
                    <div class="product-thumb product-horizontal-thumb mt-3">
                        <div id="gallery" class="product-thumb-horizontal" dir="ltr">
                            @foreach ($product->varieties as $variety)
                            <a
                                data-image="{{ $variety->images_showcase[0]->url }}"
                                data-zoom-image="{{ $variety->images_showcase[0]->url }}"
                                class="slick-slide slick-cloned active"
                            >
                                <img
                                    class="blur-up lazyload"
                                    data-src="{{ $variety->images_showcase[0]->url }}"
                                    src="{{ $variety->images_showcase[0]->url }}"
                                    alt="محصول"
                                    width="625"
                                    height="808"
                                />
                            </a>
                            @endforeach
                        </div>
                    </div>
                    <!-- End Product Thumb -->

                    <!-- Product Gallery -->
                    <div class="lightboximages">
                        <a
                        href="{{asset('front/assets/images/products/product1.jpg')}}"
                        data-size="1000x1280"
                        ></a>
                        <a
                        href="{{asset('front/assets/images/products/product1-1.jpg')}}"
                        data-size="1000x1280"
                        ></a>
                        <a
                        href="{{asset('front/assets/images/products/product1-2.jpg')}}"
                        data-size="1000x1280"
                        ></a>
                        <a
                        href="{{asset('front/assets/images/products/product1-3.jpg')}}"
                        data-size="1000x1280"
                        ></a>
                        <a
                        href="{{asset('front/assets/images/products/product1-4.jpg')}}"
                        data-size="1000x1280"
                        ></a>
                        <a
                        href="{{asset('front/assets/images/products/product1-5.jpg')}}"
                        data-size="1000x1280"
                        ></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-8 col-sm-12 col-12 product-layout-info">
                <div class="product-single-meta">
                    <h2 id="title" class="product-main-title">{{ $product->title }}</h2>
                    <div class="product-review d-flex-center mb-3">
                        <div class="reviewStar d-flex-center">
                        @for ($i = 0; $i < $averageStar; $i++)  
                            <i class="icon anm anm-star"></i>  
                        @endfor  
                        @for ($i = $averageStar; $i < 5; $i++)
                            <i class="icon anm anm-star"></i>  
                        @endfor  
                        <span class="caption me-2">24 بررسی ها</span>
                        </div>
                        <a class="reviewLink d-flex-center" href="#reviews">یک نظر بنویسید</a>
                    </div>
                    <div class="product-info">
                        <p class="product-stock d-flex">
                        دسترسی:
                        <span class="pro-stockLbl ps-0">
                            <span class="d-flex-center stockLbl instock {{$product->status == 'available' ? 'text-uppercase' : 'text-danger' }}">
                                {{$product->getStatusLabelAttribute($product->status)}} در انبار
                            </span>
                        </span>
                        </p>

                        <p class="product-type">
                        دسته بندی :<span class="text">
                        @foreach($product->categories as $category)  
                            {{ $category->title }}@if(!$loop->last), @endif  
                        @endforeach </span>
                        </p>
                        <p class="product-sku">
                        شناسه:<span class="text">{{ $product->id }}</span>
                        </p>
                    </div>
                    @php  
                        function formatPrice($price) {  
                            if ($price >= 1_000_000) {  
                                return number_format($price / 1_000_000) . ' میلیون تومان';  
                            } elseif ($price >= 1_000) {  
                                return number_format($price / 1_000) . ' هزار تومان';  
                            } else {  
                                return number_format($price) . ' تومان';  
                            }  
                        }  
                    @endphp  
                    @if ($product->discount)
                        <div class="product-price d-flex-center my-3">
                            <span class="price old-price" id="price">{{ formatPrice($product->unit_price) }}</span><span class="price">99 هزار</span>
                        </div>
                        @else
                        <div class="product-price d-flex-center my-3">
                            <span class="price" id="price">{{ formatPrice($product->unit_price) }}</span>
                        </div>
                    @endif
                </div>
                <form method="post" action="" class="product-form product-form-border hidedropdown">
                @csrf
                    <div class="product-swatches-option">
                        <div class="product-item swatches-image w-100 mb-4 swatch-0 option1"data-option-index="0">
                        {{-- <label class="label d-flex align-items-center">رنگ ها:</label> --}}
                            {{-- <ul class="variants-clr swatches d-flex-center pt-1 clearfix">
                            @foreach ($product->varieties as $variety)
                                @php
                                    $class = $variety->store_balance > 1 ? 'available' : ($variety->store_balance === 0 || is_null($variety->store_balance) ? 'soldout' : '');
                                @endphp
                                <li class="swatch x-large {{$class}} {{$loop->first ? 'active' : ''}}">
                                <img
                                    src="{{ asset('front/assets/images/products/product1-1-80x.jpg') }}"
                                    alt="تصویر"
                                    width="80"
                                    height="80"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                />
                                </li>
                            @endforeach
                            </ul> --}}
                        </div>
                        <!-- End Swatches Color -->
                        <!-- Swatches Size -->
                        <div class="product-item swatches-size w-100 mb-4 swatch-1 option2" data-option-index="1">
                        {{-- <label class="label d-flex align-items-center"
                            >اندازه:<span class="slVariant me-1 fw-bold">S</span>
                            <a
                            href="#sizechart-modal"
                            class="text-link sizelink text-muted size-chart-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#sizechart_modal"
                            >راهنمای اندازه</a
                            ></label
                        > --}}
                            <input type="hidden" id="varietyPrice" name="varietyPrice" value="">
                            <input type="hidden" id="varietyValue" name="varietyValue" value="">
                            <input type="hidden" id="imageValue" name="imageValue" value="">
                            <ul class="variants-size size-swatches d-flex-center pt-1 clearfix">
                            @if ($product->varieties_showcase['attributes'])
                                @foreach ($product->varieties_showcase['attributes'][0]['modelDetails'] as $item)  
                                    @php  
                                        $variety = Modules\Product\Entities\Variety::find($item['myAvailableVarietyIds'][0]);  
                                        $class = $variety->store_balance > 1 ? 'available' : ($variety->store_balance === 0 || is_null($variety->store_balance) ? 'soldout' : '');  
                                    @endphp  
                                    <input type="hidden" id="variety_id" name="variety_id" value="">
                                    <li class="swatch x-large {{ $class }}" data-item-id="{{ $item['myAvailableVarietyIds'][0] }}" data-item-value="{{ $item['value'] }}" data-item-price="{{ $variety['price'] }}">  
                                        <span  
                                            class="swatchLbl"  
                                            data-bs-toggle="tooltip"  
                                            data-bs-placement="top"  
                                            title="{{ $item['value'] }}"  
                                        >{{ $item['value'] }}</span>  
                                    </li>  
                                @endforeach 
                                @endif
                                {{-- <li class="swatch x-large available active">
                                <span
                                    class="swatchLbl"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="S"
                                    >S</span
                                >
                                </li>
                                <li class="swatch x-large available">
                                <span
                                    class="swatchLbl"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="M"
                                    >M</span
                                >
                                </li>
                                <li class="swatch x-large available">
                                <span
                                    class="swatchLbl"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="L"
                                    >L</span
                                >
                                </li>
                                <li class="swatch x-large available">
                                <span
                                    class="swatchLbl"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="XL"
                                    >XL</span
                                >
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                    <div class="product-action w-50 d-flex-wrap my-3 my-md-4">
                        <div class="product-form-quantity d-flex-center">
                            <div class="qtyField">
                                <a class="qtyBtn minus" onclick="decrease()">
                                    <i style="cursor: pointer" class="icon anm anm-minus-r"></i>
                                </a>
                                <input class="product-form-input qty" type="number" id="quantity" value="1" min="1" readonly>
                                <a class="qtyBtn plus" onclick="increase()">
                                    <i style="cursor: pointer" class="icon anm anm-plus-r"></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-form-submit addcart fl-1 me-3">
                        @if($product->status == 'available')
                        <button  
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#minicart-drawer"
                            name="add"  
                            class="btn btn-secondary product-form-cart-submit">  
                            <span>افزودن به سبد</span>  
                        </button>  
                        @else
                        <button
                            type="button"
                            name="add"
                            class="btn btn-secondary product-form-sold-out"
                            disabled="disabled"
                        >
                            <span>ناموجود</span>
                        </button>
                        @endif
                        </div>
                    </div>
                    <p class="infolinks d-flex-center justify-content-between">
                        @if (auth()->guard('customer')->user()->favorites()->where('product_id', $product->id)->exists())
                        <a class="text-link wishlist" style="cursor: pointer" id="wishlistBtn">  
                            <i id="favicon" class="icon anm anm-heart ms-2"></i>  
                            <span>افزودن به فهرست علاقه مندی ها</span>  
                        </a>  
                        @else
                        <a class="text-link wishlist" style="cursor: pointer" id="wishlistBtn">  
                            <i id="favicon" class="icon anm anm-heart-l ms-2"></i>  
                            <span>افزودن به فهرست علاقه مندی ها</span>  
                        </a>  
                        @endif
                    </p>
                </form>
                    <div class="modal fade" id="loginModalProduct" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">  
                        <div class="modal-dialog modal-md" role="document">  
                            <div class="modal-content">  
                                <div class="modal-header">  
                                    <h5 class="modal-title font-weight-bold">لطفاً وارد حساب کاربری خود شوید.</h5>  
                                    <button type="button" class="close" id="closeButtonProduct1" aria-label="Close">  
                                        <span aria-hidden="true">&times;</span>  
                                    </button>  
                                </div>  
                                <div class="modal-body text-center">  
                                    <a href="{{ route('pageRegisterLogin') }}" class="btn btn-primary btn-auth">ورود به حساب کاربری</a>  
                                    <button type="button" class="btn btn-outline-danger" id="closeButtonProduct2">بستن</button>  
                                </div>  
                            </div>  
                        </div>  
                    </div>
                    <div class="social-sharing d-flex-center mt-2 lh-lg">
                        <span class="sharing-lbl fw-600">اشتراک گذاری:</span>
                        <a
                            href="#"
                            class="d-flex-center btn btn-link btn--share share-facebook"
                            ><i class="icon anm anm-facebook-f"></i
                            ><span class="share-title">فیس بوک</span></a
                        >
                        <a
                            href="#"
                            class="d-flex-center btn btn-link btn--share share-twitter"
                            ><i class="icon anm anm-twitter"></i
                            ><span class="share-title">توئیت</span></a
                        >
                        <a
                            href="#"
                            class="d-flex-center btn btn-link btn--share share-pinterest"
                            ><i class="icon anm anm-pinterest-p"></i>
                            <span class="share-title">آن را پین کنید</span></a
                        >
                        <a
                            href="#"
                            class="d-flex-center btn btn-link btn--share share-linkedin"
                            ><i class="icon anm anm-linkedin-in"></i
                            ><span class="share-title">Linkedin</span></a
                        >
                        <a
                            href="#"
                            class="d-flex-center btn btn-link btn--share share-email"
                            ><i class="icon anm anm-envelope-l"></i
                            ><span class="share-title">ایمیل</span></a
                        >
                    </div>
                </div>
            </div>
        </div>
    @include('product::front.tabs-body')
    <!--End Product Tabs-->
    </div>
    <div class="pswp" tabindex="-1" role="dialog">
        <div class="pswp__bg"></div>
            <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button
                    class="pswp__button pswp__button--close"
                    title="بستن (Esc)"
                ></button>
                <button
                    class="pswp__button pswp__button--share"
                    title="اشتراک گذاری"
                ></button>
                <button
                    class="pswp__button pswp__button--fs"
                    title="تمام صفحه را تغییر دهید"
                ></button>
                <button
                    class="pswp__button pswp__button--zoom"
                    title="بزرگنمایی/کوچکنمایی"
                ></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                    <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                    </div>
                    </div>
                </div>
                </div>
                <div
                class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"
                >
                <div class="pswp__share-tooltip"></div>
                </div>
                <button
                class="pswp__button pswp__button--arrow--left"
                title="قبلی (پیکان سمت چپ)"
                ></button>
                <button
                class="pswp__button pswp__button--arrow--right"
                title="بعدی (پیکان سمت راست)"
                ></button>
                <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('product::front.suggested-products')

@endsection
@section('scripts')
    <script src="{{ asset('front/assets/js/vendor/jquery.elevatezoom.js') }}"></script>
    <script src="{{ asset('front/assets/js/vendor/photoswipe.min.js') }}"></script>
    <script>
        function formatPrice(price) {  
            if (price >= 1000000) {  
                return Math.floor(price / 1000000) + ' میلیون تومان';  
            } else if (price >= 1000) {  
                return Math.floor(price / 1000) + ' هزار تومان';  
            } else {  
                return price + ' تومان';  
            }  
        }  
        document.addEventListener('DOMContentLoaded', function() {  
            const swatchItems = document.querySelectorAll('.swatch');  
            swatchItems.forEach(item => {  
                item.addEventListener('click', function() {  
                    const itemValue = this.getAttribute('data-item-value');  
                    const itemPrice = this.getAttribute('data-item-price');  
                    const itemImage = this.getAttribute('data-image');  
                    document.getElementById('price').innerText = formatPrice(itemPrice);  
                    document.getElementById("imageValue").value = itemImage;
                    document.getElementById("varietyValue").value = itemValue;
                    document.getElementById("varietyPrice").value = itemPrice;
                });  
            });  
        });  
        function increase() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }
            
        function decrease() {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
        $(document).ready(function () {
            $('.swatch').on('click', function() {  
                var $this = $(this);  
                var selectedId = $this.data('item-id');
                var selectedPrice = document.getElementById("varietyPrice").value;
                var selectedValue = document.getElementById("varietyValue").value;
                var title = document.getElementById("title").value;
                var selectedQuantity = document.getElementById("quantity").value;
                $('#variety_id').val(selectedId);  
            });  

            updateCartDisplay();  

            $('.product-form-cart-submit').on('click', function(event) {  
                event.preventDefault();  
                let isLoggedIn = @json(auth()->guard('customer')->user());  
                
                if (!isLoggedIn) {  
                    $('#loginModalProduct').modal('show');  
                } else {  
                    let variety_id = $('#variety_id').val();  
                    let varietyQuantity = $('#quantity').val();  
                    let titleProduct = $('#title').text();  
                    let productImage = $('#imageValue').val();  
                    let varietyValue = $('#varietyValue').val();  
                    let varietyPrice = $('#price').text();  
                    $.ajax({  
                        url: `{{ route('cart.add') }}/${variety_id}`,  
                        type: 'POST',  
                        headers: {  
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },  
                        data: {  
                            variety_id: variety_id,  
                            quantity: varietyQuantity  
                        },  
                        success: function(response) {  
                            let productData = getCookie('productData');  
                            productData = productData ? JSON.parse(decodeURIComponent(productData)) : [];  

                            // اضافه کردن یا به‌روزرسانی محصول در productData  
                            const existingProduct = productData.find(product => product.variety_id === variety_id);  
                            if (existingProduct) {  
                                existingProduct.variety_quantity = parseInt(existingProduct.variety_quantity) + parseInt(varietyQuantity);  
                            } else {  
                                productData.push({  
                                    variety_id: variety_id,  
                                    variety_quantity: varietyQuantity,  
                                    title_product: titleProduct,  
                                    product_image: productImage,  
                                    variety_value: varietyValue,  
                                    variety_price: varietyPrice,  
                                });  
                            }  

                            document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  
                            
                            updateCartDisplay();  

                            Swal.fire({  
                                icon: "success",  
                                text: response.message  
                            });  
                        },  
                        error: function(error) {  
                            Swal.fire({  
                                icon: "error",  
                                text: error.responseJSON.message  
                            });  
                        }  
                    });  
                }  
            });  

            $('.spr-button-primary').on('click', function(event) {  
                event.preventDefault(); 

                let formData = {  
                    title: $('#review').val(),  
                    rate: $('#rating').val(),  
                    body: $('#message').val(),  
                    product_id: $('input[name="product_id"]').val(),  
                    show_customer_name: $('input[name="show_customer_name"]').val()  
                };  
                let isLoggedIn = @json(auth()->guard('customer')->user());  
                if (!isLoggedIn) {  
                    $('#loginModalProduct').modal('show');  
                } else {  
                    $.ajax({  
                        url: '{{ route('product-comments.store') }}', 
                        method: 'POST',  
                        headers: {  
                            'X-CSRF-TOKEN': $('input[name="_token"]').val(),  
                        },  
                        data: formData,  
                        success: function(response) {  
                            $('#commentForm')[0].reset();
                            Swal.fire({  
                                icon: "success",  
                                text: "نظر با موفقیت ثبت شد و پس از تایید نمایش داده خواهد شد."  
                            });
                        },  
                        error: function(error) {  
                            Swal.fire({  
                                icon: "error",  
                                text: error.responseJSON.message 
                            });    
                        }  
                    });  
                }  
            });  
            $('#wishlistBtn').click(function(event) {  
                event.preventDefault();  
                var formData = $('#postForm').serialize();   
                let isLoggedIn = @json(auth()->guard('customer')->user());  
                let $icon = $('favicon').find('i');  
                // if ($icon.hasClass('anm-heart-l')) {  
                    if (!isLoggedIn) {  
                        $('#loginModalProduct').modal('show');  
                    } else {  
                        $.ajax({  
                            url: `{{ route('products.addToFavorites',$idProduct) }}`,  
                            type: 'POST',  
                            headers: {  
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',  
                            },  
                            success: function(response) {
                                if ($icon.hasClass('anm-heart-l')) {  
                                    console.log(1);
                                    $icon.removeClass('anm-heart-l').addClass('anm-heart');  
                                } else {  
                                    console.log(2);
                                    $icon.removeClass('anm-heart').addClass('anm-heart-l');  
                                }    
                                Swal.fire({  
                                    icon: "success",  
                                    text: response.message  
                                });  
                            },  
                            error: function(error) {  
                                console.log(error);  
                                Swal.fire({  
                                    icon: "error",  
                                    text: error.message || "An error occurred."  
                                });  
                            }  
                        });  
                    }  
                // }else{
                //     $.ajax({  
                //         url: `{{ route('products.deleteFromFavorites', $idProduct) }}`,  
                //         type: 'DELETE',  
                //         headers: {  
                //             'X-CSRF-TOKEN': '{{ csrf_token() }}',  
                //         },  
                //         success: function(response) {
                //             let $icon = $(this).find('i');  
                //             if ($icon.hasClass('anm-heart')) {  
                //                 $icon.removeClass('anm-heart').addClass('anm-heart-l');  
                //             } else {  
                //                 $icon.removeClass('anm-heart-l').addClass('anm-heart');  
                //             }    
                //             Swal.fire({  
                //                 icon: "success",  
                //                 text: response.message  
                //             });  
                //         },  
                //         error: function(error) {  
                //             console.log(error);  
                //             Swal.fire({  
                //                 icon: "error",  
                //                 text: error.message || "An error occurred."  
                //             });  
                //         }  
                //     }); 
                // }
            }); 
            $('#closeButtonProduct1').on('click', function() {  
                $('#loginModalProduct').removeClass('show'); 
            });  
            $('#closeButtonProduct2').on('click', function() {  
                $('#loginModalProduct').removeClass('show'); 
            });  
            $('#closeButton').on('click', function() {  
                $('#loginModal').removeClass('show'); 
            });  
            $('#closeButton2').on('click', function() {  
                $('#loginModal').removeClass('show'); 
            });  
            
        });  

            //star
            const $stars = $('.star');
            const $ratingInput = $('#rating');

            $stars.on('mouseover', function() {
                const index = $(this).data('value');
                $stars.each(function(i) {
                    $(this).html(i < index ? '<i class="icon anm anm-star"></i>' : '<i class="icon anm anm-star-o"></i>');
                });
                $ratingInput.val(index);
            });

            $stars.on('mouseout', function() {
                const currentRating = parseFloat($ratingInput.val());
                $stars.each(function(i) {
                    $(this).html(i < currentRating ? '<i class="icon anm anm-star"></i>' : '<i class="icon anm anm-star-o"></i>');
                });
            });

            $stars.each(function() {
                $(this).on('click', function() {
                    const ratingValue = $(this).data('value');
                    $stars.each(function(i) {
                        $(this).html(i < ratingValue ? '<i class="icon anm anm-star"></i>' : '<i class="icon anm anm-star-o"></i>');
                    });
                });
            });
            function product_zoom() {
                $(".zoompro").elevateZoom({
                    gallery: "gallery",
                    galleryActiveClass: "active",
                    zoomWindowWidth: 300,
                    zoomWindowHeight: 100,
                    scrollZoom: false,
                    zoomType: "inner",
                    cursor: "crosshair",
                });
            }
            product_zoom();
        $(function () {
            var $pswp = $(".pswp")[0],
                image = [],
                getItems = function () {
                var items = [];
                $(".lightboximages a").each(function () {
                    var $href = $(this).attr("href"),
                    $size = $(this).data("size").split("x"),
                    item = {
                        src: $href,
                        w: $size[0],
                        h: $size[1],
                    };
                    items.push(item);
                });
                return items;
                };
            var items = getItems();

            $.each(items, function (index, value) {
                image[index] = new Image();
                image[index].src = value["src"];
            });
            $(".prlightbox").on("click", function (event) {
                event.preventDefault();

                var $index = $(".active-thumb").parent().attr("data-slick-index");
                $index++;
                $index = $index - 1;

                var options = {
                index: $index,
                bgOpacity: 0.7,
                showHideOpacity: true,
                };
                var lightBox = new PhotoSwipe(
                $pswp,
                PhotoSwipeUI_Default,
                items,
                options
                );
                lightBox.init();
            });
        });
    </script>
@endsection
