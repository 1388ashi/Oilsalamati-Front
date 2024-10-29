<div class="tab-pane" id="steps2">
  <!--مکان تحویل-->
  <div class="banks-card mt-0 h-100">
    <div
      class="top-sec d-flex-justify-center justify-content-between mb-4"
    >
      <h2 class="mb-0">مکان تحویل سفارش</h2>
      <button
        type="button"
        class="btn btn-primary btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#addCardModal"
      >
        <i class="icon anm anm-plus-r ms-1"></i> افزودن آدرس
        جدید
      </button>
      <!-- مدال آدرس جدید -->
      <div
        class="modal fade"
        id="addCardModal"
        tabindex="-1"
        aria-labelledby="addNewModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="addNewModalLabel">
                جزئیات آدرس
              </h2>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">
              <form
                class="add-address-from"
                method="post"
                action="#"
              >
                <div
                  class="form-row row-cols-lg-1 row-cols-md-2 row-cols-sm-1 row-cols-1"
                >
                  <div class="form-group">
                    <label for="new-zone" class="d-none"
                      >منطقه / ایالت
                      <span class="required">*</span></label
                    >
                    <select name="new_zone_id" id="new-zone">
                      <option value="">
                        منطقه / ایالت را انتخاب کنید
                      </option>
                      <option value="AL">آلاباما</option>
                      <option value="AK">آلاسکا</option>
                      <option value="AZ">آریزونا</option>
                      <option value="AR">آرکانزاس</option>
                      <option value="CA">کالیفرنیا</option>
                      <option value="CO">کلرادو</option>
                      <option value="CT">کانکتیکات</option>
                      <option value="DE">دلاور</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="new-country" class="d-none"
                      >کشور
                      <span class="required">*</span></label
                    >
                    <select
                      name="new_country_id"
                      id="new-country"
                    >
                      <option value="">
                        کشور را انتخاب کنید
                      </option>
                      <option value="AI" label="آنگویلا">
                        آنگویلا
                      </option>
                      <option
                        value="AG"
                        label="آنتیگوا و باربودا"
                      >
                        آنتیگوا و باربودا
                      </option>
                      <option value="AR" label="آرژانتین">
                        آرژانتین
                      </option>
                      <option value="AW" label="آروبا">
                        آروبا
                      </option>
                      <option value="BS" label="باهاما">
                        باهاما
                      </option>
                      <option value="BB" label="باربادوس">
                        باربادوس
                      </option>
                      <option value="BZ" label="بلیز">
                        بلیز
                      </option>
                      <option value="BM" label="برمودا">
                        برمودا
                      </option>
                      <option value="BO" label="بولیوی">
                        بولیوی
                      </option>
                      <option value="BR" label="برزيل">
                        برزيل
                      </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="new-name" class="d-none"
                      >نام</label
                    >
                    <input
                      name="name"
                      placeholder="نام"
                      value=""
                      id="new-name"
                      type="text"
                    />
                  </div>
                  <div class="form-group">
                    <label for="new-name" class="d-none"
                      >نام خانوادگی</label
                    >
                    <input
                      name="name"
                      placeholder="نام خانوادگی"
                      value=""
                      id="new-name"
                      type="text"
                    />
                  </div>
                  <div class="form-group">
                    <label for="new-company" class="d-none"
                      >کد پستی</label
                    >
                    <input
                      name="company"
                      placeholder="کد پستی"
                      value=""
                      id="new-company"
                      type="text"
                    />
                  </div>
                  <div class="form-group">
                    <label for="new-apartment" class="d-none"
                      >موبایل
                      <span class="required">*</span></label
                    >
                    <input
                      name="apartment"
                      placeholder="موبایل"
                      value=""
                      id="new-apartment"
                      type="text"
                    />
                  </div>
                  <div class="form-group">
                    <label
                      for="new-street-address"
                      class="d-none"
                      >آدرس
                      <span class="required">*</span></label
                    >
                    <input
                      name="street_address"
                      placeholder="آدرس خیابان"
                      value=""
                      id="new-street-address"
                      type="text"
                    />
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="submit" class="btn btn-primary m-0">
                <span>افزودن آدرس</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End New Address Modal -->
    </div>

    <div class="bank-book-section">
      <div
        class="row g-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1"
      >
        <div class="address-select-box active">
          <div class="address-box bg-block">
            <div class="middle">
              <div class="card-number mb-3">
                <div class="customRadio clearfix">
                  <input
                    id="formcheckoutRadio1"
                    value=""
                    name="radio1"
                    type="radio"
                    class="radio"
                    checked="checked"
                  />
                  <label for="formcheckoutRadio1" class="mb-2"
                    >گلستان - گرگان - گرگان شهرک</label
                  >

                  <p class="text-muted">
                    محمد رضا ملک شاهکویی - 09113707857
                  </p>
                  <p class="text-muted">کد پستی : 4915785112</p>
                </div>
              </div>
            </div>
            <div class="bottom d-flex-justify-left">
              <button
                type="button"
                class="bottom-btn btn btn-primery btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#addCardModal"
              >
                ویرایش
              </button>
              <button
                type="button"
                class="bottom-btn btn btn-secondary btn-sm"
              >
                حذف
              </button>
            </div>
          </div>
        </div>
        <div class="address-select-box active">
          <div class="address-box bg-block">
            <div class="middle">
              <div class="card-number mb-3">
                <div class="customRadio clearfix">
                  <input
                    id="formcheckoutRadio2"
                    value=""
                    name="radio1"
                    type="radio"
                    class="radio"
                    checked="checked"
                  />
                  <label for="formcheckoutRadio2" class="mb-2"
                    >گلستان - گرگان - گرگان شهرک</label
                  >

                  <p class="text-muted">
                    محمد رضا ملک شاهکویی - 09113707857
                  </p>
                  <p class="text-muted">کد پستی : 4915785112</p>
                </div>
              </div>
            </div>
            <div class="bottom d-flex-justify-left">
              <button
                type="button"
                class="bottom-btn btn btn-primery btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#addCardModal"
              >
                ویرایش
              </button>
              <button
                type="button"
                class="bottom-btn btn btn-secondary btn-sm"
              >
                حذف
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--مکان تحویل-->

  <!-- شیوه ارسال-->
  <div class="banks-card mt-3 h-100">
    <div
      class="top-sec d-flex-justify-center justify-content-between mb-4"
    >
      <h2 class="mb-0">شیوه ارسال</h2>
    </div>

    <div class="bank-book-section">
      <div
        class="row g-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1"
      >
        <div class="address-select-box active">
          <div class="address-box bg-block">
            <div
              class="top bank-logo d-flex-justify-center justify-content-between mb-3"
            >
              <img
                src="assets/images/icons/bank-logo1.png"
                class="bank-logo"
                width="40"
                alt=""
              />
            </div>
            <div class="middle">
              <div class="card-number mb-3">
                <div class="customRadio clearfix">
                  <input
                    id="formcheckoutRadio4"
                    value=""
                    name="radio1"
                    type="radio"
                    class="radio"
                    checked="checked"
                  />
                  <label for="formcheckoutRadio4" class="mb-2"
                    >تیپاکس</label
                  >
                  <p class="text-muted">
                    هزینه ارسال کالا از یک قلم تا ده قلم 55 الی
                    75 هزار تومان میباشد
                  </p>
                </div>
              </div>
              <div
                class="name-validity d-flex-justify-center justify-content-between"
              >
                <div class="left">
                  <h6>هزینه ارسال</h6>
                </div>
                <div class="right">
                  <h6>10000 تومان</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="address-select-box active">
          <div class="address-box bg-block">
            <div
              class="top bank-logo d-flex-justify-center justify-content-between mb-3"
            >
              <img
                src="assets/images/icons/bank-logo1.png"
                class="bank-logo"
                width="40"
                alt=""
              />
            </div>
            <div class="middle">
              <div class="card-number mb-3">
                <div class="customRadio clearfix">
                  <input
                    id="formcheckoutRadio3"
                    value=""
                    name="radio1"
                    type="radio"
                    class="radio"
                    checked="checked"
                  />
                  <label for="formcheckoutRadio3" class="mb-2"
                    >پست پیشتاز</label
                  >
                  <p class="text-muted">
                    هزینه ارسال کالا از یک قلم تا ده قلم 55 الی
                    75 هزار تومان میباشد
                  </p>
                </div>
              </div>
              <div
                class="name-validity d-flex-justify-center justify-content-between"
              >
                <div class="left">
                  <h6>هزینه ارسال</h6>
                </div>
                <div class="right">
                  <h6>10000 تومان</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- شیوه ارسال-->

  <div class="d-flex justify-content-between mt-4">
    <button
      type="button"
      class="btn btn-secondary btnPrevious ms-1"
    >
      مرحله قبل
    </button>
    <button type="button" class="btn btn-primary me-1 btnNext">
      مرحله بعد
    </button>
  </div>
</div>