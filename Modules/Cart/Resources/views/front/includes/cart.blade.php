<div class="tab-pane active" id="steps1">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-7 col-lg-8">
      <!--خلاصه سفارش-->
      <div class="block order-summary">
        <div class="block-content">
          <h3 class="title mb-3">سبد خرید</h3>
          <div class="table-responsive table-bottom-brd order-table">
            <table id="CartsTable" class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th class="action">&nbsp;</th>
                  <th class="text-end">تصویر</th>
                  <th class="text-end proName">محصول</th>
                  <th class="text-center">تعداد</th>
                  <th class="text-center">قیمت واحد</th>
                  <th class="text-center">تخفیف واحد</th>
                  <th class="text-center">مجموع</th>
                </tr>
              </thead>
              <tbody>

                @php
                  $totalPrice = 0;
                  $totalDiscount = 0;
                  $totalPriceWithDiscount = 0;
                @endphp

                @forelse ($carts as $cart)
                  <tr id="Cart-{{ $cart->id }}">
                    <td class="text-center cart-delete">
                      <button
                        class="btn btn-secondary cart-remove remove-icon position-static"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        onclick="confirmDelete('delete-cart-{{ $cart->id }}')" 
                        title="حذف از سبد">
                        <i class="icon anm anm-times-r"></i>
                      </button>
                      <form
                        action="{{ route('cart.remove', $cart) }}"
                        id="delete-cart-{{ $cart->id }}"
                        method="POST"
                        style="display: none;">
                        @csrf
                        @method("DELETE")
                      </form>
                    </td>
                    <td class="text-end">
                      <a href="{{ route('products.show', $cart->variety->product_id) }}" class="thumb">

                        @php
                          $imageUrl = $cart->variety->product->images_showcase['main_image']->url;
                        @endphp

                        <img
                        <img
                          class="rounded-0 blur-up lazyload"
                          data-src="{{ asset($imageUrl) }}"
                          src="{{ asset($imageUrl) }}"
                          alt="محصول"
                          title="محصول"
                          width="120"
                          height="170"
                        />
                        />

                      </a>
                    </td>
                    <td class="text-end proName">
                      <div class="list-view-item-title">
                        <a href="{{ route('products.show', $cart->variety->product_id) }}">{{ $cart->variety->title }}</a>
                      </div>
                    </td>
                    <td class="cart-update-wrapper cart-flex-item text-end text-md-center">
                      <div class="cart-qty d-flex justify-content-end justify-content-md-center">
                        <div class="qtyField">
                          <span class="qtyBtn minus"><i class="icon anm anm-minus-r"></i></span>
                          <input class="cart-qty-input qty" type="text" data-cart-id="{{ $cart->id }}" value="{{ $cart->quantity }}" pattern="[0-9]*" />  
                          <span class="qtyBtn plus"><i class="icon anm anm-plus-r"></i></span>
                        </div>
                      </div>
                      <a title="حذف" class="removeMb d-md-none d-inline-block text-decoration-underline mt-2 ms-3">حذف</a>
                    </td>
                    <td class="text-center"><span class="unit-price">{{ number_format($cart->price) }}</span> تومان</td>
                    <td class="text-center"><span class="unit-discount">{{ number_format($cart->discount_price) }}</span> تومان</td>
                    <td class="text-center">
                      <strong>
                        <span class="price">{{ number_format(($cart->price - $cart->discount_price) * $cart->quantity) }}</span>
                        تومان
                      </strong>
                    </td>
                  </tr>

                  @php
                    $totalPrice += $cart->quantity * $cart->price;
                    $totalDiscount += $cart->quantity * $cart->discount_price;
                    $totalPriceWithDiscount += (($cart->price - $cart->discount_price) * $cart->quantity);
                  @endphp

                @empty
                <tr>
                  <td colspan="7" class="text-center"> 
                    <span class="text-danger">هیچ محصولی در سبد شما نیست !</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!--پایان خلاصه سفارش-->
    </div>
    <div class="col-12 col-sm-12 col-md-5 col-lg-4">
      <div class="block mb-3 application-code mb-4">
        <div class="block-content">
          <h3 class="title mb-3">اعمال کد تبلیغاتی</h3>
          <div id="coupon" class="coupon-dec">
            <p>کد تخفیف خود را وارد کنید.</p>
            <div class="input-group mb-0 d-flex">
              <input id="coupon-code" type="text" class="form-control" placeholder="کد تبلیغاتی/تخفیف"/>
              <button class="coupon-btn btn btn-primary" id="coupon-code-button" type="button">اعمال</button>
            </div>
          </div>
        </div>
      </div>
      <div class="cart-info mb-4">
        <div class="cart-order-detail cart-col">
          <div class="row g-0 border-bottom pb-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"><strong>مبلغ سفارش</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"><span class="money" id="SumPrice">{{ number_format($totalPrice) }} تومان</span></span>
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"><strong>تخفیف</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"><span class="money" id="SumDiscount">{{ number_format($totalDiscount) }} تومان</span></span>
          </div>
          <div class="row g-0 pt-2">
            <span class="col-6 col-sm-6 cart-subtotal-title fs-6"><strong>مجموع</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title fs-5 cart-subtotal text-end text-primary"><b class="money" id="SumPriceWithDiscount">{{ number_format($totalPriceWithDiscount) }} تومان</b></span>
          </div>
        </div>
      </div>
      <!--خلاصه سبد خرید-->
    </div>
  </div>

  <div class="d-flex justify-content-end">
    {{-- <button
      type="button"
      class="btn btn-secondary ms-1 btnPrevious"
    >
      بازگشت
    </button> --}}
    <button type="button" class="btn btn-primary me-1" id="steps1-btnNext">
      ادامه فرآیند خرید
    </button>
  </div>
</div>