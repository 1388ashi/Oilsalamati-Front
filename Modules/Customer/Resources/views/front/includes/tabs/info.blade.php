<div class="tab-pane fade h-100 show active" id="info">
  <div class="account-info h-100">
    <div class="row g-3 row-cols-lg-3 row-cols-md-3 row-cols-sm-3 row-cols-1 mb-4">
      <div class="counter-box">
        <div class="bg-block d-flex-center flex-nowrap">
          <img class="blur-up lazyload" src="{{asset('front/assets/images/icons/sale.png')}}" alt="آیکن" width="64" height="64"/>
          <div class="content">
            <h3 class="fs-5 mb-1 text-primary">{{ $customer->orders_count }}</h3>
            <p>کل سفارشات </p>
          </div>
        </div>
      </div>
      <div class="counter-box">
        <div class="bg-block d-flex-center flex-nowrap">
          <img class="blur-up lazyload" src="{{asset('front/assets/images/icons/homework.png')}}" alt="آیکن" width="64" height="64"/>
          <div class="content">
            <h3 class="fs-5 mb-1 text-primary">{{ $pendingOrdersCount }}</h3>
            <p>سفارشات معلق</p>
          </div>
        </div>
      </div>
      <div class="counter-box">
        <div class="bg-block d-flex-center flex-nowrap">
          <img class="blur-up lazyload" src="{{asset('front/assets/images/icons/order.png')}}" alt="آیکن" width="64" height="64"/>
          <div class="content">
            <h3 class="fs-5 mb-1 text-primary">{{ $deliveredOrdersCount }}</h3>
            <p>تکمیل شده</p>
          </div>
        </div>
      </div>
    </div>

    <div class="account-box">
      <div class="row">
        <div class="box-info mb-4">
          <div class="box-title d-flex justify-content-between align-items-center">
            <h4>اطلاعات حساب</h4>
            <button 
              type="button" 
              onclick="editProfile(event)" 
              class="btn btn-primary fw-light"
              style="font-size: 12px; padding: 4px 12px;"
              >بروزرسانی</button>
          </div>
          <div class="box-content mt-3">
            <div class="row">
              <form id="EditProfileForm" action="{{ route('customer.profile.update') }}" method="PATCH" class="col-12">
                <div class="row">
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>نام :</label>
                      <input type="text" class="form-control first-name" value="{{ $customer->first_name }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>نام خانوادگی :</label>
                      <input type="text" class="form-control last-name" value="{{ $customer->last_name }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>موبایل :</label>
                      <input type="text" class="form-control mobile" value="{{ $customer->mobile }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>ایمیل :</label>
                      <input type="email" class="form-control email" value="{{ $customer->email }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>شماره کارت :</label>
                      <input type="text" class="form-control card-number" value="{{ $customer->card_number }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>تاریخ تولد :</label>
                      <input type="text" class="form-control birth-date" value="{{ $customer->birth_date }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>کد ملی :</label>
                      <input type="text" class="form-control national-code" value="{{ $customer->national_code }}">
                    </div>
                  </div>
                  <div class="col-xl-6 col-12">
                    <div class="form-group">
                      <label>جنسیت :</label>
                      <select class="form-control gender">
                        <option value="male" @if ($customer->gender === 'male') selected @endif>مرد</option>
                        <option value="female" @if ($customer->gender === 'female') selected @endif>زن</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-group">
                      <button class="btn btn-primary form-control w-100">بروزرسانی</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>  
      </div>
    </div>
  </div>
</div>