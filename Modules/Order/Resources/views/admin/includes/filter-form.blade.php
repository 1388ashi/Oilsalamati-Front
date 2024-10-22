<div class="card">
  <div class="card-header border-0">
    <p class="card-title">جستجوی پیشرفته</p>
  </div>
  <div class="card-body">
    <div class="row">
      <form action="{{ route('admin.orders.index') }}" class="col-12">
        <div class="row">

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="number" placeholder="شناسه" name="id" class="form-control" value="{{ request('id') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="number" placeholder="کد رهگیری" name="tracking_code" class="form-control" value="{{ request('tracking_code') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="text" placeholder="شهر" name="city" class="form-control" value="{{ request('city') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="text" placeholder="استان" name="province" class="form-control" value="{{ request('province') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="text" placeholder="نام" name="first_name" class="form-control" value="{{ request('first_name') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="text" placeholder="نام خانوادگی" name="last_name" class="form-control" value="{{ request('last_name') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <select name="customer_id" class="form-control search-customer-ajax"></select>
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <select name="status" class="form-control" id="status">
                <option value="">انتخاب وضعیت</option>
                @foreach ($allOrderStatuses as $statusName)
                  <option
                    value="{{ $statusName }}"
                    {{ request('status') == $statusName ? 'selected' : ''}}>
                    {{ __('statuses.' . $statusName) }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input class="form-control fc-datepicker" id="start_date_show" type="text" placeholder="از تاریخ"/>
              <input name="start_date" id="start_date_hide" type="hidden" value="{{ request("start_date") }}"/>
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input class="form-control fc-datepicker" id="end_date_show" type="text" placeholder="تا تاریخ"/>
              <input name="end_date" id="end_date_hide" type="hidden" value="{{ request("end_date") }}"/>
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="number" name="invoices_amount_from" placeholder="مبلغ سفارش از" class="form-control" value="{{ request('invoices_amount_from') }}">
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="form-group">
              <input type="number" name="invoices_amount_to" placeholder="مبلغ سفارش تا" class="form-control" value="{{ request('invoices_amount_to') }}">
            </div>
          </div>

        </div>
        <div class="row">

          <div class="col-12 col-md-6 col-xl-9">
            <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.orders.index') }}"
               class="btn btn-danger btn-block">حذف همه فیلتر ها <i class="fa fa-close"></i></a>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>