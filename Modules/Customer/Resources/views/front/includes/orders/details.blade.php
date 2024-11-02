{{-- <div class="modal fade" id="OrderDetail-{{ $order->id }}" tabindex="-1" aria-labelledby="OrderDetail-{{ $order->id }}Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="OrderDetail-{{ $order->id }}Label">جزئیات سفارش</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-bottom-brd table-responsive">
          <table class="table align-middle text-center order-table">
            <thead>
              <tr class="table-head text-nowrap">
                <th scope="col">ردیف</th>
                <th scope="col">تصویر </th>
                <th scope="col">محصول </th>
                <th scope="col">تعداد</th>
                <th scope="col">تخفیف واحد</th>
                <th scope="col">قیمت واحد</th>
                <th scope="col">قیمت کل</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($order->items as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td class="d-flex align-items-center justify-content-center">
                    @php
                      $imageUrl = $item->product->images_showcase['main_image']->url;
                    @endphp
                    <a href="{{ asset($imageUrl) }}" class="thumb">
                      <img
                        class="rounded-0 blur-up lazyloaded"
                        src="{{ asset($imageUrl) }}"
                        alt="محصول"
                        title="محصول"
                        width="120"
                        height="170"
                      />
                    </a>
                  </td>
                  <td>{{ $item->variety->title }}</td>
                  <td>{{ $item->quantity }}</td>
                  <td>{{ number_format($item->discount_amount) }}</td>
                  <td>{{ number_format($item->amount) }}</td>
                  <td>{{ number_format(($item->amount - $item->discount_amount) * $item->quantity) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center">آیتمی یافت نشد</td>
                </tr>
                @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div> --}}
