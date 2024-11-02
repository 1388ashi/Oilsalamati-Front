<div class="tab-pane fade h-100" id="favorites">
  <div class="orders-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">لیست آرزوهای من</h2>
    </div>

    <div class="table-bottom-brd table-responsive">
      <table class="table align-middle text-center order-table">
        <thead>
          <tr class="table-head text-nowrap">
            <th scope="col">تصویر</th>
            <th scope="col">شناسه سفارش</th>
            <th scope="col">جزئیات محصول</th>
            <th scope="col">قیمت</th>
            <th scope="col">اقدام</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($customer->favorites as $product)
          <tr>
            <td>
              <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-120x170.jpg')}}"
                src="{{asset('front/assets/images/products/product1-120x170.jpg')}}"
                width="50"
                alt="محصول"
                title="محصول"
              />
            </td>
            <td><span class="id">#12301</span></td>
            <td>
              <span class="name">پیراهن کوبایی آکسفورد</span>
            </td>
            <td>
              <span class="price fw-500">99.00 تومان </span>
            </td>
            <td>
              <a href="cart-style1.html" class="btn btn-md text-nowrap">افزودن به سبد خرید</a>
            </td>
          </tr>
          @empty
              
          @endforelse
          <tr>
            <td>
              <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-120x170.jpg')}}"
                src="{{asset('front/assets/images/products/product1-120x170.jpg')}}"
                width="50"
                alt="محصول"
                title="محصول"
              />
            </td>
            <td><span class="id">#12301</span></td>
            <td>
              <span class="name">پیراهن کوبایی آکسفورد</span>
            </td>
            <td>
              <span class="price fw-500">99.00 تومان </span>
            </td>
            <td>
              <a
                href="cart-style1.html"
                class="btn btn-md text-nowrap"
                >افزودن به سبد خرید</a
              >
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>