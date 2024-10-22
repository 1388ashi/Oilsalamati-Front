<div class="row border" style="margin-bottom: 30px;">
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
