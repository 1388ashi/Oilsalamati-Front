<div class="tab-pane fade h-100" id="orders">
  <div class="orders-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">سفارشات من</h2>
      <a id="print-user-orders-button" target="_blank" href="{{ route('customer.print-orders') }}" style="cursor: pointer;">
        <span>چاپ تمام سفارشات</span>
      </a>
    </div>
    <div class="table-bottom-brd table-responsive">
      <table class="table align-middle text-center order-table">
        <thead>
          <tr class="table-head text-nowrap">
            <th scope="col">ردیف</th>
            <th scope="col">شماره سفارش </th>
            <th scope="col">تعداد اقلام </th>
            <th scope="col">مبلغ (تومان)</th>
            <th scope="col">وضعیت</th>
            <th scope="col">تاریخ</th>
            <th scope="col">ساعت</th>
            <th scope="col">مشاهده</th>
            <th scope="col">چاپ</th>
          </tr>
        </thead>
        <tbody>

          @php
            $statusColors = [
              'wait_for_payment' => 'bg-warning',
              'new' => 'bg-primary',
              'in_progress' => 'bg-info',
              'delivered' => 'bg-success',
              'canceled' => 'bg-danger',
              'failed' => 'bg-danger',
              'reserved' => 'bg-warning',
              'canceled_by_user' => 'bg-danger',
            ];
          @endphp

          @forelse ($customer->orders->sortByDesc('id') as $order)
            <tr>
              <td class="fw-bold">{{ $loop->iteration }}</td>
              <td>{{ $order->id }}</td>
              <td>{{ $order->items->count() }}</td>
              <td>{{ number_format($order->total_invoices_amount) }}</td>
              <td>
                <span class="badge rounded-pill {{ $statusColors[$order->status] }} custom-badge">{{ __('statuses.' . $order->status) }}</span>
              </td>
              <td>{{ verta($order->created_at)->format('Y/m/d') }}</td>
              <td>{{ verta($order->created_at)->formatTime() }}</td>
              <td>
                <a class="view" data-bs-toggle="modal" style="cursor: pointer;" data-bs-target="#OrderDetail-{{ $order->id }}"><i class="fe fe-eye text-info fs-18"></i></a>
                @include('customer::front.includes.orders.details')
              </td>
              <td>
                <a target="_blank" href="{{ route('customer.print-orders', ['order_id' => $order->id]) }}" style="cursor: pointer;"><i class="fe fe-printer text-purple fs-18"></i></a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center text-danger">سفارشی ثبت نشده است !</td>
            </tr>
            @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>