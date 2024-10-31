<div class="tab-pane fade" id="steps3">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-7 col-lg-8">
      <div class="block mb-3 delivery-methods mb-4">
        <div class="block-content">
          <h3 class="title mb-3">نوع سفارش</h3>
          <div class="delivery-methods-content">
            <div class="customRadio clearfix">
              <input id="order-type-radio" value="1" type="radio" class="radio order-type-input" checked/>
              <label for="order-type-radio" class="mb-0">عادی</label>
            </div>
          </div>
        </div>
      </div>
      <div class="block mb-3 payment-methods mb-4">
        <div class="block-content">
          <h3 class="title mb-3">نوع پرداخت</h3>
          <div class="payment-accordion-radio">
            <div class="accordion" id="accordionExample">
              <div class="accordion-item card mb-2">
                <div class="card-header" id="headingThree">
                  <button class="card-link" type="button" data-bs-toggle="collapse" data-bs-target="#walletColapse" aria-expanded="false" aria-controls="walletColapse">
                    <span class="customRadio clearfix mb-0">
                      <input id="pay-type-wallet-radio" value="wallet" name="pay_type" type="radio" class="radio pay-type-input"/>
                      <label for="pay-type-wallet-radio" class="mb-0">پرداخت از کیف پول (موجودی فعلی: {{ number_format($user->wallet->balance) }} تومان)</label>
                    </span>
                  </button>
                </div>
                <div id="walletColapse" class="accordion-collapse collapse" aria-labelledby="headingThree" >
                  <div class="card-body px-0">
                    <p>لطفاً چک خود را به نام فروشگاه، خیابان فروشگاه، شهرک فروشگاه، ایالت/شهرستان فروشگاه، کدپستی فروشگاه ارسال کنید.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item card mb-2">
                <div class="card-header" id="headingFour">
                  <button class="card-link" type="button" data-bs-toggle="collapse" data-bs-target="#gatewayColapse" aria-expanded="false" aria-controls="gatewayColapse" >
                    <span class="customRadio clearfix mb-0">
                      <input id="pay-type-gateway-radio" value="gateway" name="pay_type" type="radio" class="radio pay-type-input"/>
                      <label for="pay-type-gateway-radio" class="mb-0">پرداخت اینترنتی</label>
                    </span>
                  </button>
                </div>
                <div id="gatewayColapse" class="accordion-collapse collapse" aria-labelledby="headingFour">
                  <div class="card-body px-0">
                    <div class="address-select-box active">
                      <div class="address-box bg-block">
                        <div class="middle">
                          @foreach ($drivers as $driver)
                            <div class="card-number mb-3">
                              <div class="customRadio clearfix">
                                <input id="driver-radio-{{ $driver['name'] }}" value="{{ $driver['name'] }}" name="payment_driver" type="radio" class="radio driver-input"/>
                                <label for="driver-radio-{{ $driver['name'] }}" class="mb-2">{{ $driver['label'] }}</label>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="accordion-item card mb-0">
                <div class="card-header" id="headinFive">
                  <button class="card-link" type="button" data-bs-toggle="collapse" data-bs-target="#bothClolapse" aria-expanded="false" aria-controls="bothClolapse">
                    <span class="customRadio clearfix mb-0">
                      <input id="pay-type-both-radio" value="both" name="pay_type" type="radio" class="radio pay-type-input"/>
                      <label for="pay-type-both-radio" class="mb-0">کیف پول و درگاه</label>
                    </span>
                  </button>
                </div>
                <div id="bothClolapse" class="accordion-collapse collapse" aria-labelledby="headinFive" >
                  <div class="card-body px-0">
                    <p>با انتخاب این گزینه ابتدا مبلغ سفارش از کیف پول شما کسر می شود و مابقی آن از طریق درگاه انتخاب شده.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-12 col-md-5 col-lg-4">
      <!--خلاصه سبد خرید-->
      <div class="cart-info">
        <div class="cart-order-detail cart-col" id="FinalCartOrderDetail">
          <div class="row g-0 border-bottom pb-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"><strong>مجموع قیمت کالا ها</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"><span class="total-price-amount"></span> تومان</span>
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"><strong>تخفیف کالا ها</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"><span class="total-discount-amount"></span> تومان</span>
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"><strong>تخفیف کوپن</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"><span class="coupon-amount"></span> تومان</span>
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"><strong>هزینه ارسال</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"><span class="shipping-amount"></span> تومان</span>
          </div>
          <div class="row g-0 pt-2">
            <span class="col-6 col-sm-6 cart-subtotal-title fs-6"><strong>مجموع</strong></span>
            <span class="col-6 col-sm-6 cart-subtotal-title fs-5 cart-subtotal text-end text-primary"><b class="total"></b> تومان</span>
          </div>

          <button type="button" id="cartCheckout" class="btn btn-lg my-4 checkout w-100">ثبت سفارش</button>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-start mt-4">
    <button type="button" class="btn btn-secondary ms-1" id="steps3-btnPrevious">مرحله قبل</button>
  </div>

</div>