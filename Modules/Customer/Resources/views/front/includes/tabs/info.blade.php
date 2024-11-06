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
          <div class="box-title d-flex-center">
            <h4>اطلاعات حساب</h4>
            <a class="btn-link me-auto" data-bs-toggle="modal" data-bs-target="#EditProfileModal" style="cursor: pointer;">ویرایش</a>
            @include('customer::front.includes.info.edit-profile-modal')
          </div>
          <div class="box-content mt-3">

            @php
              $gender = [
                'male' => 'مرد',
                'female' => 'زن',
                null => '-'
              ];
            @endphp

            <div class="row">
              <div class="col-xl-6 col-12">
                <p>نام و نام خانوادگی: <b class="info-full-name">{{ $customer->full_name ?? '-' }}</b></p>
                <p>موبایل: <b class="info-mobile">{{ $customer->mobile }}</b></p>
                <p>ایمیل: <b>{{ $customer->email ?? '-' }}</b></p>
                <p>شماره کارت: <b class="info-card-number">{{ $customer->card_number ?? '-' }}</b></p>
              </div>
              <div class="col-xl-6 col-12">
                <p>تاریخ تولد: <b>{{ $customer->birth_date ? verta($customer->birth_date)->formatDate() : '-' }}</b></p>
                <p>کد ملی: <b>{{ $customer->national_code ?? '-' }}</b></p>
                <p>جنسیت: <b>{{ $gender[$customer->gender] }}</b></p>
              </div>
            </div>

          </div>
        </div>  
      </div>
    </div>
  </div>
</div>