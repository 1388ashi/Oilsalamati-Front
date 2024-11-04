@if(isset($relatedProducts1[0]))
<section class="section product-slider pb-0">
    <div class="container">
        <div class="section-header">
            <h2>محصولات مشترک</h2>
        </div>
        <div class="grid-products product-slider-4items gp15 arwOut5 hov-arrow" dir="ltr">
            @foreach ($relatedProducts1 as $product)
            <div class="item col-item">
                <div class="product-box">
                    <div class="product-image">
                        <!-- شروع تصویر محصول -->
                        <a href="{{route('products.show',$product->id)}}" class="product-img">
                            <img
                                class="primary blur-up lazyload"
                                data-src="{{asset('front/assets/images/products/cosmetic-product1.jpg')}}"
                                src="{{asset('front/assets/images/products/cosmetic-product1.jpg')}}"
                                alt="محصول"
                                title="محصول"
                                width="625"
                                height="703"
                            />
                            <img
                                class="hover blur-up lazyload"
                                data-src="{{asset('front/assets/images/products/cosmetic-product1-1.jpg')}}"
                                src="{{asset('front/assets/images/products/cosmetic-product1-1.jpg')}}"
                                alt=" محصول"
                                title="محصول"
                                width="625"
                                height="703"
                            />
                        </a>
                        @php($finalPrice = $product->major_final_price)
                        @php($hasDiscount = $finalPrice->discount_price > 0 ? true : false)
                        <div class="product-labels">
                            @if ($finalPrice->discount_type === 'percentage')
                                <span class="lbl on-sale">{{ $finalPrice->discount . '%' }} تخفیف</span>
                            @elseif($finalPrice->discount_type)
                                <span class="lbl on-sale">{{ number_format($finalPrice->discount_price) . ' تومان' }} تخفیف</span>
                            @endif
                        </div>
                        <!-- برچسب محصول نهایی -->
                    </div>
                    <div class="product-details text-center">
                        <!-- نام محصول -->
                        <div class="product-name">
                        <a href="{{route('products.show',$product->id)}}">{{$product->title}}</a>
                        </div>
                        <!-- نام محصول نهایی -->
                        <!-- قیمت محصول -->
                        <div class="product-price">
                            @if ($hasDiscount)
                                <span class="price old-price">{{ number_format($finalPrice->amount) }} تومان </span>
                            @endif
                            <span class="price">{{ number_format($finalPrice->amount) }} تومان </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif