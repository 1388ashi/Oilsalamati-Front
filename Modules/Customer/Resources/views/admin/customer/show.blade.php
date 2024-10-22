@extends('admin.layouts.master')
@section('content')
  @php
    $genders = [
      'male' => 'مرد',
      'female' => 'زن',
      null => null,
    ];
  @endphp
  @php
    $items = [
      ['title' => 'لیست مشتریان', 'route_link' => 'admin.customers.index'],
      ['title' => 'اطلاعات مشتری', 'route_link' => null]
    ];
  @endphp
  <x-breadcrumb :items="$items"/>
  @include('components.errors')
  <x-card>
    <x-slot name="cardTitle">اطلاعات مشتری</x-slot>
    <x-slot name="cardOptions">
      <div class="card-options" class="d-flex" style="gap: 5px;">
        <button data-target="#increment-wallet-balance-modal" data-toggle="modal"class="btn btn-outline-success btn-sm"> افزایش موجودی</button>
        <button data-target="#decrement-wallet-balance-modal" data-toggle="modal"class="btn btn-outline-danger btn-sm">کاهش موجودی</button>
      </div>
    </x-slot>
    <x-slot name="cardBody">
      <div class="row">
        @php
          $customerDetails = [
            ['key' => 'نام و نام خانوادگی', 'value' =>  $customer->first_name . ' ' . $customer->last_name],
            ['key' => 'شماره موبایل', 'value' =>  $customer->mobile],
            ['key' => 'ایمیل', 'value' =>  $customer->email],
            ['key' => 'تاریخ تولد', 'value' =>  $customer->birth_date ? verta($customer->birth_date)->format('Y/m/d') : '-'],
            ['key' => 'شماره کارت', 'value' =>  $customer->card_number],
            ['key' => 'کد ملی', 'value' =>  $customer->national_code],
            ['key' => 'جنسیت', 'value' =>  $genders[$customer->gender]],
            ['key' => 'موجودی کیف پول (تومان)', 'value' =>  number_format($customer->wallet->balance)],
            ['key' => 'مقدار هدیه (تومان)', 'value' =>  number_format($balance_gift)],
          ];
        @endphp
        @foreach ($customerDetails as $detail)
          <div class="col-12 col-xl-4 col-lg-6 my-1">
            <strong> {{ $detail['key'] }} : </strong>
            <span> {{ $detail['value'] }} </span>
          </div>
        @endforeach
      </div>
    </x-slot>
  </x-card>

  <x-card>
    <x-slot name="cardTitle">لیست آدرس ها</x-slot>
    <x-slot name="cardOptions">
      <div class="card-options" class="d-flex" style="gap: 5px;">
        <button data-target="#createAddresseModal" data-toggle="modal"class="btn btn-outline-primary btn-sm"> آدرس جدید</button>
      </div>
    </x-slot>
    <x-slot name="cardBody">
      <div class="row">
        <x-table-component>
          <x-slot name="tableTh">
            <tr>
              <th>ردیف</th>
              <th>شناسه</th>
              <th>آدرس</th>
              <th>گیرنده</th>
              <th>موبایل</th>
              <th>عملیات</th>
            </tr>
          </x-slot>
          <x-slot name="tableTd">
            @forelse ($customer->addresses->sortByDesc('id') as $address)
              <tr>
                <td class="font-weight-bold">{{ $loop->iteration }}</td>
                <td>{{ $address->id }}</td>
                <td>{{ $address->address }}</td>
                <td>{{ $address->first_name . ' ' . $address->last_name }}</td>
                <td>{{ $address->mobile}}</td>
                <td>
                  @include('core::includes.edit-modal-button',[
                    'target' => "#edit-address-" . $address->id
                  ])
                  <button
                    onclick="confirmDelete('delete-{{ $address->id }}')"
                    class="btn btn-sm btn-icon btn-danger text-white"
                    data-toggle="tooltip"
                    data-original-title="حذف">
                    <i class="fa fa-trash-o"></i>
                  </button>
                  <form
                    action="{{ route('admin.addresses.destroy',[$customer, $address]) }}"
                    method="POST"
                    id="delete-{{ $address->id }}"
                    style="display: none">
                    @csrf
                    @method('DELETE')
                  </form>
                </td>
              </tr>
            @empty
              @include('core::includes.data-not-found-alert', ['colspan' => 6])
            @endforelse
          </x-slot>
          <x-slot name="extraData"></x-slot>
        </x-table-component>
      </div>
    </x-slot>
  </x-card>

  <x-card>
    <x-slot name="cardTitle">تراکنش های کیف پول</x-slot>
    <x-slot name="cardOptions"></x-slot>
    <x-slot name="cardBody">
      <div class="row">
        <x-table-component>
          <x-slot name="tableTh">
            <tr>
              <th>ردیف</th>
              <th>شناسه</th>
              <th>مبلغ (تومان)</th>
              <th>نوع</th>
              <th>وضعیت</th>
              <th>شناسه پرداخت</th>
              <th>تاریخ</th>
            </tr>
          </x-slot>
          <x-slot name="tableTd">
            @forelse ($transactions as $transaction)
              <tr>
                <td class="font-weight-bold">{{ $loop->iteration }}</td>
                <td>{{ $transaction->id }}</td>
                <td>{{ number_format(abs($transaction->amount)) }}</td>
                <td>
                  @if($transaction->type == 'deposit')
                    <span title="نوع" class="badge badge-success-light">افزایش کیف پول</span>
                  @else
                    <span title="نوع" class="badge badge-danger-light">برداشت از کیف پول</span>
                  @endif
                </td>
                <td>
                  @if($transaction->confirmed)
                    <span title="وضعیت" class="badge badge-success ">موفقیت آمیز</span>
                  @else
                    <span title="وضعیت" class="badge badge-danger">خطا</span>
                  @endif
                </td>
                <td>{{ $transaction->payable_id }}</td>
                <td>{{verta($transaction->created_at)->format('Y/m/d H:i')}}</td>
              </tr>
            @empty
              @include('core::includes.data-not-found-alert', ['colspan' => 7])
            @endforelse
          </x-slot>
          <x-slot name="extraData"></x-slot>
        </x-table-component>
      </div>
    </x-slot>
  </x-card>
  
  @include('customer::admin.customer.includes.create-address-modal')
  @include('customer::admin.customer.includes.edit-address-modal')
  @include('customer::admin.customer.includes.decrement-wallet-balance-modal')
  @include('customer::admin.customer.includes.increment-wallet-balance-modal')

@endsection

@section('scripts')
  <script>
      $(document).ready(function () {
          $('#province_id').change(function () {
              var provinceId = $(this).val();

              $('#city_id').empty();
              $('#city_id').append('<option value="">شهر را انتخاب کنید</option>');
              $('#city_id_container').hide();

              if (provinceId) {
                  $.ajax({
                      url: '{{ route("admin.getCity") }}',
                      data: {provinceId: provinceId},
                      type: 'GET',
                      success: function (data) {
                          $.each(data, function (index, city) {
                              $('#city_id').append('<option value="' + city.id + '">' + city.name + '</option>');
                          });

                          $('#city_id_container').show();
                      },
                      error: function () {
                          alert('خطا در بارگذاری شهرها.');
                      }
                  });
              }
          });
      });
  </script>
@endsection
