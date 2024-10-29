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
                @foreach ($carts as $cart)
                <tr id="Cart-{{ $cart->id }}">
                  <td class="text-center cart-delete">
                    <a
                      href="#"
                      class="btn btn-secondary cart-remove remove-icon position-static"
                      data-bs-toggle="tooltip" 
                      data-bs-placement="top" 
                      title="حذف از سبد">
                      <i class="icon anm anm-times-r"></i>
                    </a>
                  </td>
                  <td class="text-end">
                    <a href="{{ route('products.show', $cart->variety->product_id) }}" class="thumb">

                      @if ($cart->variety->images_showcase)
                        @php($imageUrl = $cart->variety->images_showcase['main_image']->url)
                      @else
                        @php($imageUrl = $cart->variety->product->images_showcase['main_image']->url)
                      @endif

                      <img
                        class="rounded-0 blur-up lazyload"
                        data-src="{{ asset($imageUrl) }}"
                        src="{{ asset($imageUrl) }}"
                        alt="محصول"
                        title="محصول"
                        width="120"
                        height="170"
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
                        <input   
                          class="cart-qty-input qty"   
                          type="text"   
                          data-cart-id="{{ $cart->id }}"
                          name="updates[]"   
                          value="{{ $cart->quantity }}"   
                          pattern="[0-9]*"   
                        />  
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
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!--پایان خلاصه سفارش-->
    </div>
    <div class="col-12 col-sm-12 col-md-5 col-lg-4">
      <!--اعمال کد تبلیغاتی-->
      <div class="block mb-3 application-code mb-4">
        <div class="block-content">
          <h3 class="title mb-3">اعمال کد تبلیغاتی</h3>
          <div id="coupon" class="coupon-dec">
            <p>کد تخفیف خود را وارد کنید.</p>
            <div class="input-group mb-0 d-flex">
              <input
                id="coupon-code"
                required
                type="text"
                class="form-control"
                placeholder="کد تبلیغاتی/تخفیف"
              />
              <button
                class="coupon-btn btn btn-primary"
                type="submit"
              >
                اعمال
              </button>
            </div>
          </div>
        </div>
      </div>
      <!--End Apply Promocode-->
      <!--خلاصه سبد خرید-->
      <div class="cart-info mb-4">
        <div class="cart-order-detail cart-col">
          <div class="row g-0 border-bottom pb-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"
              ><strong>زیر مجموع</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
              ><span class="money">326.00 تومان</span></span
            >
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"
              ><strong>تخفیف کوپن</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
              ><span class="money">-25.00 تومان</span></span
            >
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"
              ><strong>مالیات</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
              ><span class="money">10.00 تومان</span></span
            >
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"
              ><strong>ارسال</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
              ><span class="money">ارسال رایگان</span></span
            >
          </div>
          <div class="row g-0 pt-2">
            <span
              class="col-6 col-sm-6 cart-subtotal-title fs-6"
              ><strong>مجموع</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title fs-5 cart-subtotal text-end text-primary"
              ><b class="money">311.00 تومان</b></span
            >
          </div>
        </div>
      </div>
      <!--خلاصه سبد خرید-->
    </div>
  </div>

  <div class="d-flex justify-content-between">
    <button
      type="button"
      class="btn btn-secondary ms-1 btnPrevious"
    >
      بازگشت
    </button>
    <button type="button" class="btn btn-primary me-1 btnNext">
      ادامه فرآیند خرید
    </button>
  </div>
</div>