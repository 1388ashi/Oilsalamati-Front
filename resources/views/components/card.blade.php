<div class="card {{ isset($col) ? $col : null }}">
    <div class="card-header border-0">
        <p class="card-title font-weight-bold">{{ $cardTitle }}</p>
        {{ $cardOptions }}
    </div>
    <div class="card-body">
        {{ $cardBody }}
    </div>
</div>