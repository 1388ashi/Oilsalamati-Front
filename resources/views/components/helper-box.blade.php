<div class="{{ isset($col) ? $col : 'col-xl-3 col-lg-6 col-md-12' }}">
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="{{ isset($icon) ? 'col-9' : 'col-12' }}">
          <div class="mt-0 text-right">
            <span class="fs-14 font-weight-bold"> {{ $title }} : </span>
            <p class="mb-0 mt-1 text-{{ $color }} fs-16"> {{ $amount }}  </p>
          </div>
        </div>
        @if (isset($icon))
          <div class="col-3">
            <div class="icon1 bg-{{ $color }}-transparent my-auto float-left">
              <i class="fa fa-{{ $icon }}"></i>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>