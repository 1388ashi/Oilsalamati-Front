<div class="row" style="margin-top: 20px;">
  <p class="header fs-20 text-center border border-bottom-0 w-100 mb-0 font-weight-bold"> محصولات</p>
  <div class="col-12">
    <div class="row">
      <div class="table-responsive">
        <table role="table" aria-busy="false" aria-colcount="9" class="table b-table table-striped table-bordered ">
          <thead role="rowgroup" class="text-center">
          <tr role="row">
            <th>ردیف</th>
            <th>شناسه</th>
            <th>محصول</th>
            <th>کمپین</th>
            <th>مبلغ واحد (تومان)</th>
            <th>تخفیف واحد (تومان)</th>
            <th>تعداد</th>
            <th>مبلغ کل (تومان)</th>
            <th>تخفیف کل (تومان)</th>
            <th>مبلغ با تخفیف (تومان)</th>
          </tr>
          </thead>
          <tbody class="text-center">
          @php($totalPrice = 0)
          @foreach($order->items as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->id }}</td>
              <td>{{ $item->variety->title }}</td>
              <td>{{ $item->flash->title ?? '-' }}</td>
              <td>{{ number_format($item->amount) }}</td>
              <td>{{ number_format($item->discount_amount) }}</td>
              <td>{{ $item->quantity }}</td>
              <td>{{ number_format($item->amount * $item->quantity) }}</td>
              <td>{{ number_format($item->discount_amount * $item->quantity) }}</td>
              <td>{{ number_format(($item->amount - $item->discount_amount) * $item->quantity) }}</td>
            </tr>
            @php($totalPrice += (($item->amount - $item->discount_amount) * $item->quantity))
          @endforeach
            <tr class="bg-dark text-white fs-17">
              <td colspan="9">جمع کل :</td>
              <td class="font-weight-bold" colspan="1">{{number_format($totalPrice)}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

