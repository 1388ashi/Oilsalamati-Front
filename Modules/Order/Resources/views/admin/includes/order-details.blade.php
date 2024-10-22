@php
  $statusColors = [
      'wait_for_payment' => 'warning',
      'new' => 'primary',
      'in_progress' => 'secondary',
      'delivered' => 'success',
      'canceled' => 'danger',
      'failed' => 'danger',
      'reserved' => 'info',
      'canceled_by_user' => 'danger',
  ];
  $genders = [
      'male' => 'مرد',
      'female' => 'زن',
      null => null,
  ];
@endphp

<div class="row border" id="order-details" style="display: none;">
  <p class="header fs-20 text-center w-100 mb-0 font-weight-bold">جزئیات سفارش</p>
  <div class="col-12 mt-2">
    <div class="row">
      <div class="col-lg-3 my-2"><strong>شناسه :</strong><span>{{ $order->id }}</span></div>
      <div class="col-lg-3 my-2"><strong>تاریخ
          ثبت:</strong><span>{{ verta($order->created_at)->format('Y/m/d H:i:s') }}</span></div>
      <div class="col-lg-3 my-2">
        <strong>وضعیت سفارش :</strong>
        <span class="text-white px-2 bg-{{ $statusColors[$order->status] }}"
              style="border-radius: 6px">{{ __('statuses.' . $order->status) }}</span>
        <button class="border-0 mr-1 px-2 badge-warning text-white" data-target="#edit-order-status-modal"
                data-toggle="modal" style="border-radius: 6px">
          <i class="fe fe-edit"></i>
        </button>
      </div>
      <div class="col-lg-3 my-2"><strong>تاریخ تحویل
          :</strong><span>{{ verta($order->delivered_at)->format('Y/m/d H:i:s') }}</span></div>
      <div class="col-lg-3 my-2">
        <strong>شیوه ارسال :</strong>
        <span>{{ $order->shipping->name }}</span>
      </div>
      <div class="col-lg-3 my-2">
        <strong>رزرو شده : </strong>
        @if ($order->reserved)
          <span><i class="text-success fa fa-check-circle-o"></i></span>
        @else
          <span><i class="text-danger  fa fa-close"></i></span>
        @endif
      </div>
      <div class="col-lg-3 my-2"><strong>توضیحات : </strong> {{ $order->description }} </div>
    </div>
  </div>
</div>

<div class="row border" id="customer-details" style="display: none;">
  <p class="header fs-20 text-center w-100 mb-0 font-weight-bold">اطلاعات مشتری</p>
  <div class="col-12 mt-2">
    <div class="row">
      <div class="col-lg-3 my-2">
        <span><strong>شناسه : </strong> {{ $order->customer->id }}</span>
        <a href="{{ route('admin.customers.show', $order->customer) }}"
           class="btn outline btn-outline-info py-0 px-2 mr-2">
          <i class="fa fa-eye"></i>
        </a>
      </div>
      <div class="col-lg-3 my-2"><strong>نام و نام خانوادگی : </strong> {{ $order->customer->full_name }} </div>
      <div class="col-lg-3 my-2"><strong>ایمیل : </strong> {{ $order->email }} </div>
      <div class="col-lg-3 my-2"><strong>تاریخ تولد : </strong>
        {{ $order->customer->birth_date ? verta($order->customer->birth_date)->format('Y/m/d') : null }}
      </div>
      <div class="col-lg-3 my-2"><strong>جنسیت : </strong> {{ $genders[$order->customer->gender] }} </div>
      <div class="col-lg-3 my-2"><strong>شماره کارت : </strong> {{ $order->customer->card_number }} </div>
      <div class="col-lg-3 my-2">
        <span>
            <strong>موجودی کیف پول : </strong>
            @if ($order->customer->wallet->balance)
            <span>{{ number_format($order->customer->wallet->balance) }} تومان</span>
          @else
            <span class="text-danger">موجودی ندارد</span>
          @endif
        </span>
      </div>
    </div>
  </div>
</div>

<div class="row border" id="receiver-details" style="display: none;">
  <p class="header fs-20 text-center w-100 mb-0 font-weight-bold">اطلاعات دریافت کننده</p>
  @php $addressJSON = json_decode($order->address); @endphp
  <div class="col-12 mt-2">
    <div class="row">
      <div class="col-lg-3 my-2">
        <strong>نام و نام خانوادگی :</strong>
        <span>{{ $addressJSON->first_name . ' ' . $addressJSON->last_name }}</span>
      </div>
      <div class="col-lg-3 my-2">
        <strong>موبایل :</strong>
        <span>{{ $addressJSON->mobile }}</span>
      </div>
      <div class="col-lg-3 my-2">
        <strong>کد پستی :</strong>
        <span>{{ $addressJSON->postal_code }}</span>
      </div>
      <div class="col-12 my-2">
        <strong>آدرس :</strong>
        <span>{{ $addressJSON->address }}</span>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="edit-order-status-modal" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content modal-content-demo">
      <div class="modal-header">
        <p class="modal-title">تغییر وضعیت سفارش</p>
        <button aria-label="Close" class="close" data-dismiss="modal"><span
                  aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">

          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-12 my-1">
              <strong class="fs-15">وضعیت فعلی: </strong><span>{{ __('statuses.' . $order->status) }}</span>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="form-group">
                <select name="status" id="order_status" class="form-control">
                  <option value="">انتخاب وضعیت</option>
                  @foreach ($orderStatuses as $status)
                    @if ($status != $order->status)
                      <option value="{{ $status }}">{{ __('statuses.' . $status) }}</option>
                    @endif
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <textarea id="description" class="form-control" rows="2" name="description"
                          placeholder="توضیحات">{{ old('description') }}</textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer justify-content-center">
            <button class="btn btn-outline-warning" type="submit">ویرایش</button>
            <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

{{-- <div class="modal fade" id="EditOrderModal" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content modal-content-demo">
      <div class="modal-header">
        <p class="modal-title">ویرایش سفارش</p>
        <button aria-label="Close" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin.orderUpdater.showcase', $order->id) }}" method="POST">

          @csrf

          <div class="row">
            <div class="col-12 my-1">
              <strong class="fs-15">حمل و نقل فعلی: </strong><span>{{ $order->shipping->name }}</span>
            </div>
            <div class="col-12 my-1">
              <strong class="fs-15">آدرس فعلی: </strong><span>{{ $addressJSON->address }}</span>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="form-group">
                <select name="newAddress_id" id="address_id" class="form-control">
                  <option value=""></option>
                  @foreach ($addresses as $address)
                    <option value="{{ $address->id }}">{{ $address->address }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <select name="newShipping_id" id="shipping_id" class="form-control">
                  <option value=""></option>
                  @foreach ($shippings as $shipping)
                    <option value="{{ $shipping->id }}">{{ $shipping->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <select name="pay_type" class="form-control pay_type">
                  <option value=""></option>
                  <option value="gateway">درگاه</option>
                  <option value="wallet">کیف پول</option>
                  <option value="both">هر دو</option>
                </select>
              </div>
            </div>

          </div>

          <div class="modal-footer justify-content-center">
            <button class="btn btn-outline-warning" type="submit">بروزرسانی</button>
            <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="AddItemToOrderModal" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content modal-content-demo">
      <div class="modal-header">
        <p class="modal-title">افزودن محصول به سفارش</p>
        <button aria-label="Close" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin.orderUpdater.showcase', $order->id) }}" method="POST">
          @csrf

          <div class="row mt-3">

            <div class="col-12">
              <div class="form-group">
                <select class="form-control search-product-ajax select2" id="products"></select>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <select id="variety" class="form-control select2"></select>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <select name="pay_type" class="form-control pay_type">
                  <option value=""></option>
                  <option value="gateway">درگاه</option>
                  <option value="wallet">کیف پول</option>
                  <option value="both">هر دو</option>
                </select>
              </div>
            </div>

          </div>

         <div class="row">
          <div id="products-discount-section" class="col-12 mx-auto table-responsive mt-4">
            <table id="products-discount-table" role="table"
                   class="table b-table table-hover table-bordered text-center border-top">
              <thead role="rowgroup">
              <tr role="row">
                <th role="columnheader" scope="col" aria-colindex="1">محصول</th>
                <th role="columnheader" scope="col" aria-colindex="2">تنوع</th>
                <th role="columnheader" scope="col" aria-colindex="3">قیمت (تومان)</th>
                <th role="columnheader" scope="col" aria-colindex="4">تعداد</th>
                <th role="columnheader" scope="col" aria-colindex="5">عملیات</th>
              </tr>
              </thead>
              <tbody role="rowgroup">

              </tbody>
            </table>
          </div>
         </div>

          <div class="modal-footer justify-content-center">
            <button class="btn btn-bitbucket" type="submit">صدور صورتحساب</button>
            <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div> --}}
