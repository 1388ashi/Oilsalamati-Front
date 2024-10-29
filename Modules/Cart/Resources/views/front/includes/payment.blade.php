<div class="tab-pane fade" id="steps3">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-7 col-lg-8">
      <!--روش های تحویل-->
      <div class="block mb-3 delivery-methods mb-4">
        <div class="block-content">
          <h3 class="title mb-3">نوع سفارش</h3>
          <div class="delivery-methods-content">
            <div class="customRadio clearfix">
              <input
                id="formcheckoutRadio5"
                value=""
                name="radio1"
                type="radio"
                class="radio"
                checked="checked"
              />
              <label for="formcheckoutRadio5" class="mb-0"
                >عادی</label
              >
            </div>
            <div class="customRadio clearfix">
              <input
                id="formcheckoutRadio6"
                value=""
                name="radio1"
                type="radio"
                class="radio"
              />
              <label for="formcheckoutRadio6" class="mb-0"
                >رزرو</label
              >
            </div>
          </div>
        </div>
      </div>
      <!--پایان روش های تحویل-->
      <!--روش های پرداخت-->
      <div class="block mb-3 payment-methods mb-4">
        <div class="block-content">
          <h3 class="title mb-3">نوع سفارش</h3>
          <div class="payment-accordion-radio">
            <div class="accordion" id="accordionExample">
              <div class="accordion-item card mb-2">
                <div class="card-header" id="headingThree">
                  <button
                    class="card-link"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseThree"
                    aria-expanded="false"
                    aria-controls="collapseThree"
                  >
                    <span class="customRadio clearfix mb-0">
                      <input
                        id="paymentRadio3"
                        value=""
                        name="payment"
                        type="radio"
                        class="radio"
                      />
                      <label for="paymentRadio3" class="mb-0"
                        >پرداخت از کیف پول (موجودی فعلی: 0
                        تومان)</label
                      >
                    </span>
                  </button>
                </div>
                <div
                  id="collapseThree"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingThree"
                  data-bs-parent="#accordionExample"
                >
                  <div class="card-body px-0">
                    <p>
                      لطفاً چک خود را به نام فروشگاه، خیابان
                      فروشگاه، شهرک فروشگاه، ایالت/شهرستان
                      فروشگاه، کدپستی فروشگاه ارسال کنید.
                    </p>
                  </div>
                </div>
              </div>
              <div class="accordion-item card mb-0">
                <div class="card-header" id="headingFour">
                  <button
                    class="card-link"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseFour"
                    aria-expanded="false"
                    aria-controls="collapseFour"
                  >
                    <span class="customRadio clearfix mb-0">
                      <input
                        id="paymentRadio4"
                        value=""
                        name="payment"
                        type="radio"
                        class="radio"
                      />
                      <label for="paymentRadio4" class="mb-0"
                        >پرداخت اینترنتی</label
                      >
                    </span>
                  </button>
                </div>
                <div
                  id="collapseFour"
                  class="accordion-collapse collapse"
                  aria-labelledby="headingFour"
                  data-bs-parent="#accordionExample"
                >
                  <div class="card-body px-0">
                    <div class="address-select-box active">
                      <div class="address-box bg-block">
                        <div class="middle">
                          <div class="card-number mb-3">
                            <div class="customRadio clearfix">
                              <input
                                id="formcheckoutRadio20"
                                value=""
                                name="radio1"
                                type="radio"
                                class="radio"
                                checked="checked"
                              />
                              <label
                                for="formcheckoutRadio20"
                                class="mb-2"
                                >بانک ملی</label
                              >
                            </div>
                          </div>
                          <div class="card-number mb-3">
                            <div class="customRadio clearfix">
                              <input
                                id="formcheckoutRadio10"
                                value=""
                                name="radio1"
                                type="radio"
                                class="radio"
                                checked="checked"
                              />
                              <label
                                for="formcheckoutRadio10"
                                class="mb-2"
                                >بانک ملت</label
                              >
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--پایان دادن به روش های پرداخت-->
    </div>
    <div class="col-12 col-sm-12 col-md-5 col-lg-4">
      <!--خلاصه سبد خرید-->
      <div class="cart-info">
        <div class="cart-order-detail cart-col">
          <div class="row g-0 border-bottom pb-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"
              ><strong>زیر مجموع</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
              ><span class="money">226.00 تومان</span></span
            >
          </div>
          <div class="row g-0 border-bottom py-2">
            <span class="col-6 col-sm-6 cart-subtotal-title"
              ><strong>تخفیف کوپن</strong></span
            >
            <span
              class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
              ><span class="money">-25.00تومان</span></span
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

          <a
            href="order-success.html"
            id="cartCheckout"
            class="btn btn-lg my-4 checkout w-100"
            >ثبت سفارش</a
          >
        </div>
      </div>
      <!--خلاصه سبد خرید-->
    </div>
  </div>
</div>