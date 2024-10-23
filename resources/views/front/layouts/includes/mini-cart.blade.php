<div id="minicart-drawer" class="minicart-right-drawer offcanvas offcanvas-start" tabindex="-1">
  <!--MiniCart Empty-->
  <div id="cartEmpty" class="cartEmpty d-flex-justify-center flex-column text-center p-3 text-muted d-none">
    <div class="minicart-header d-flex-center justify-content-between w-100">
      <h4 class="fs-6">سبد خرید شما (0 مورد)</h4>
      <button class="close-cart border-0" data-bs-dismiss="offcanvas" aria-label="Close" >
        <i class="icon anm anm-times-r" data-bs-toggle="tooltip " data-bs-placement="left" title="بستن"></i>
      </button>
    </div>
    <div class="cartEmpty-content mt-4">
      <i class="icon anm anm-cart-l fs-1 text-muted"></i>
      <p class="my-3">هیچ محصولی در سبد خرید وجود ندارد</p>
      <a href="index.html" class="btn btn-primary cart-btn">ادامه خرید</a>
    </div>
  </div>
  <!--پایان دادن MiniCart Empty-->

  <!--محتوای MiniCart-->
  <div id="cart-drawer" class="block block-cart">
    <div class="minicart-header">
      <button class="close-cart border-0" data-bs-dismiss="offcanvas" aria-label="Close">
        <i class="icon anm anm-times-r" data-bs-toggle="tooltip " data-bs-placement="left" title="بستن"></i>
      </button>
      <h4 class="fs-6">سبد خرید شما (2 مورد)</h4>
    </div>
    <div class="minicart-content">
      <ul class="m-0 clearfix">
        <li class="item d-flex justify-content-center align-items-center">
          <a class="product-image rounded-0" href="product-layout1.html">
            <img
              class="rounded-0 blur-up lazyload"
              data-src="assets/images/products/cart-product-img1.jpg"
              src="assets/images/products/cart-product-img1.jpg"
              alt="product"
              title="محصول"
              width="120"
              height="170"
            />
          </a>
          <div class="product-details">
            <a class="product-title" href="product-layout1.html">صندل زنانه</a>
            <div class="variant-cart my-2">مشکی / XL</div>
            <div class="priceRow">
              <div class="product-price">
                <span class="price">54.00 تومان</span>
              </div>
            </div>
          </div>
          <div class="qtyDetail text-center">
            <div class="qtyField">
              <a class="qtyBtn minus" href="#"><i class="icon anm anm-minus-r"></i></a>
              <input type="text" name="quantity" value="1" class="qty" />
              <a class="qtyBtn plus" href="#"><i class="icon anm anm-plus-r"></i></a>
            </div>
            <a href="#" class="edit-i remove">
              <i class="icon anm anm-pencil-ar" data-bs-toggle="tooltip" data-bs-placement="top" title=" ویرایش"></i>
            </a>
            <a href="#" class="remove">
              <i class="icon anm anm-times-r" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف"></i>
            </a>
          </div>
        </li>
        <li class="item d-flex justify-content-center align-items-center">
          <a class="product-image rounded-0" href="product-layout1.html">
            <img
              class="rounded-0 blur-up lazyload"
              data-src="assets/images/products/cart-product-img2.jpg"
              src="assets/images/products/cart-product-img2.jpg"
              alt="product"
              title="محصول"
              width="120"
              height="170"
            />
          </a>
          <div class="product-details">
            <a class="product-title" href="product-layout1.html"
              >شلوار جین کمر بلند</a
            >
            <div class="variant-cart my-2">زرد / M</div>
            <div class="priceRow">
              <div class="product-price">
                <span class="price old-price">114.00 تومان</span
                ><span class="price">99.00 تومان</span>
              </div>
            </div>
          </div>
          <div class="qtyDetail text-center">
            <div class="qtyField">
              <a class="qtyBtn minus" href="#"
                ><i class="icon anm anm-minus-r"></i
              ></a>
              <input type="text" name="quantity" value="1" class="qty" />
              <a class="qtyBtn plus" href="#"
                ><i class="icon anm anm-plus-r"></i
              ></a>
            </div>
            <a href="#" class="edit-i remove"
              ><i
                class="icon anm anm-pencil-ar"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title=" ویرایش"
              ></i
            ></a>
            <a href="#" class="remove"
              ><i
                class="icon anm anm-times-r"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="حذف"
              >
              </i
            ></a>
          </div>
        </li>
      </ul>
    </div>
    <div class="minicart-bottom">
      <div class="subtotal clearfix my-3">
        <div class="totalInfo clearfix">
          <span>تخفیف:</span
          ><span class="item product-price">120.00 تومان</span>
        </div>
        <div class="totalInfo clearfix">
          <span>مجموع:</span
          ><span class="item product-price">163.00 تومان</span>
        </div>
      </div>

      <div class="minicart-action d-flex mt-3">
        <a
          href="checkout-style1.html"
          class="proceed-to-checkout btn btn-primary w-100 ms-1"
          >تکمیل خرید</a
        >
      </div>
    </div>
  </div>
  <!--محتوای MiniCart-->
</div>