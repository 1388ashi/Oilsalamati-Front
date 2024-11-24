<!doctype html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>چاپ سفارشات</title>
    @include("admin.layouts.includes.styles")

    <style>
      .bg-gray-print {
        background-color: #DDDDDD;
      }
      .text-navy-blue {
        color: #263871;
      }
      .border-print {
        border: 1px solid #BF9B74;
      }
      .radius-8px {
        border-radius: 8px;
      }
      .fs-15 {
        font-size: 15px;
      }
      @media print {
        .page-break {
          page-break-before: always;
          page-break-after: always;
        }
      }
    </style>

  </head>
  <body class="mt-5" style="background: white;">

    @foreach ($orders as $order)

      <div class="page-break" style="padding-inline: 8%;">

        <div class="col-12">
          <div class="position-relative d-flex justify-content-between align-items-center">
            <span>شماره سفارش : <b>{{ $order->id }}</b></span>
            <span>تاریخ : <b>{{ verta()->format('Y/m/d H:i') }}</b></span>
          </div>
        </div>

        <div class="col-12 d-flex mt-4" style="gap: 5px;">

          <div class="d-flex justify-content-center align-items-center bg-gray-print border-print radius-8px" style="width: 7%;">
            <span class="font-weight-bold text-navy-blue fs-15">گیرنده</span>
          </div>

          <div class="px-4 py-2 border-print radius-8px" style="width: 93%; display: grid;">

            @php
              $address = json_decode($order->address);
              $city = $address->city->name;
              $province = $address->city->province->name;
            @endphp

            <span>نام کامل : <b>{{ $address->first_name .' '. $address->last_name }}</b></span>
            <span>آدرس : <b>{{ $city .' - '. $province }} - {{ $address->address }}</b></span>
            <span class="d-flex" style="gap: 12px;">
              <span>کد پستی : <b>{{ $address->mobile }}</b></span>
              <span>موبایل : <b>{{ $address->postal_code }}</b></span>
            </span>

          </div>
        </div>

        @if ($order->description)
          <div class="col-12 d-flex mt-4" style="gap: 5px;">

            <div class="d-flex justify-content-center align-items-center bg-gray-print border-print radius-8px" style="width: 7%;">
              <span class="font-weight-bold text-navy-blue fs-15">توضیحات</span>
            </div>

            <div class="px-4 py-2 border-print radius-8px" style="width: 93%; display: grid;">
              <p style="margin-bottom: 0;">{{ $order->description }}</p>
            </div>
          </div>
        @endif

        <div class="col-12 mt-3">
          <table class="table table-vcenter text-center table-striped text-nowrap table-bordered border-bottom card-table">
            <thead style="background-color: #EAEAEA">
              <tr>
                <th>ردیف</th>
                <th>محصول</th>
                <th>مبلغ واحد (تومان)</th>
                <th>تخفیف واحد (تومان)</th>
                <th>تعداد</th>
                <th>مبلغ کل (تومان)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($order->items as $item)
                <tr>
                  <td class="font-weight-bold">{{ $loop->iteration }}</td>
                  <td>{{ $item->variety->title }}</td>
                  <td>{{ number_format($item->amount) }}</td>
                  <td>{{ number_format($item->discount_amount) }}</td>
                  <td>{{ $item->quantity }}</td>
                  <td>{{ number_format(($item->amount - $item->discount_amount) * $item->quantity) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center px-3 py-4 border-print radius-8px" style="height: 36px;">
            <span>مبلغ فاکتور : <b>{{ number_format($order->total_invoices_amount) }}</b> تومان</span>
            <span>هزینه حمل و نقل : <b>{{ number_format($order->shipping_amount) }}</b> تومان</span>
            <span>جمع کل پرداختی : <b>{{ number_format($order->shipping_amount + $order->total_invoices_amount) }}</b> تومان</span>
          </div>
        </div>
        
      </div>

      <div class="d-print-none" style="margin-top: 4%; margin-bottom: 4%;">
        <div class="col-12">
          <div class="d-flex justify-content-center">
            <button class="btn btn-purple" onclick="window.print()">چاپ</button>
          </div>
        </div>
      </div>

      <div class="d-print-none" style="margin-top: 100px;"></div>
      
    @endforeach

    

    @include("admin.layouts.includes.scripts")

  </body>
</html>