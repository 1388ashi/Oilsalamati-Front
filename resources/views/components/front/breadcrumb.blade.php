@section('breadcrumb')
<div class="col-12 col-sm-12 col-md-12 col-lg-12">
    <!--Breadcrumbs-->
    <div class="breadcrumbs">
        <a href="{{ route('home') }}" title="برگشت به صفحه اصلی">صفحه اصلی</a>
        @foreach ($items as $item)
        @if (!isset($item['route_link']) || is_null($item['route_link']))
            <span class="main-title fw-bold">
                <i class="icon anm anm-angle-left-l"></i>
                {{ $item['title'] }}
            </span>
            @else
            <span class="main-title fw-bold">
                <i class="icon anm anm-angle-left-l"></i>
                    @php
                        $param = isset($item['parameter']) ? $item['parameter'] : null;
                    @endphp
                    <a href="{{ route($item['route_link'], $param) }}">{{ $item['title'] }}</a>
                </span>
        @endif
        @endforeach
    </div>
<!--End Breadcrumbs-->
</div>
@endsection