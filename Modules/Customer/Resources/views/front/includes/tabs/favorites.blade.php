<div class="tab-pane fade h-100" id="favorites">
  <div class="orders-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">لیست علاقه مندی های من</h2>
    </div>

    <div class="table-bottom-brd table-responsive">
      <table class="table align-middle text-center order-table">
        <thead>
          <tr class="table-head text-nowrap">
            <th scope="col">حذف</th>
            <th scope="col">تصویر</th>
            <th scope="col">شناسه سفارش</th>
            <th scope="col">جزئیات محصول</th>
            <th scope="col">قیمت</th>
            {{-- <th scope="col">اقدام</th> --}}
          </tr>
        </thead>
        <tbody>
          @forelse ($customer->favorites as $product)
          <tr>
            <td>
              <button
              onclick="removeProduct(event)"
              class="btn btn-secondary cart-remove remove-icon position-static"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              data-url="{{ route('products.deleteFromFavorites', $product->id) }}"
              title="حذف از سبد خرید"
              ><i class="icon anm anm-times-r"></i></button>
            </td>
            <td>
              <img
              class="blur-up lazyload"
              data-src="{{$product->images_showcase['main_image']->url}}"
              src="{{$product->images_showcase['main_image']->url}}"
              width="50"
              alt="محصول"
              title="محصول"
            />
            </td>
            <td><span class="id">{{$product->id}}</span></td>
            <td>
              <span class="name">{{$product->title}}</span>
            </td>
            <td>
              <span class="price fw-500">{{number_format($product->price)}}</span>
            </td>
            {{-- <td>
              <a href="cart-style1.html" class="btn btn-md text-nowrap">افزودن به سبد خرید</a>
            </td> --}}
          </tr>
          @empty
          <tr class="text-center">
            <td colspan="5" class="text-danger">آیتمی جهت نمایش وجود ندارد</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>