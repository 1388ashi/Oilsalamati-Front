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
      <h4 id="cart-count" class="fs-6">سبد خرید شما (0 مورد)</h4>  
    </div>
    <div class="minicart-content">
      <ul class="m-0 clearfix">
        <div id="output"></div>  
      </ul>
    </div>
    <div class="minicart-bottom">
      <div class="subtotal clearfix my-3">
        <div class="totalInfo clearfix">
          <span>تخفیف:</span
          ><span id="cart-discount" class="item product-price">0</span>
        </div>
        <div class="totalInfo clearfix">
          <span>مجموع:</span
          ><span id="cart-price" class="item product-price">0</span>
        </div>
      </div>

      <div class="minicart-action d-flex mt-3">
        <a
          href="{{route('cart.index')}}"
          class="proceed-to-checkout btn btn-primary w-100 ms-1"
          >تکمیل خرید</a
        >
      </div>
    </div>
  </div>
  <!--محتوای MiniCart-->
</div>