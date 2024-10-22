<div class="{{ $cssClasses }}">
    <div class="form-group">
		@if ($hasLabel == 'true')<label>انتخاب محصول :</label>@endif
        <select class="form-control" id="{{ $productInputId }}" name="{{ $productInputName }}">
            <option value="">انتخاب</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" {{ request($productInputName) == $product->id ? 'selected' : null }}>
                    {{ $product->title }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="{{ $cssClasses }}">
    <div class="form-group">
		@if ($hasLabel == 'true')<label>انتخاب تنوع :</label>@endif
        <select name="{{ $varietyInputName }}" id="{{ $varietyInputId }}" class="form-control">
			<option value="">انتخاب</option>
        </select>
    </div>
</div>