@foreach($units as $unit)
<x-modal id="edit-unit-{{ $unit->id }}" size="md">
    <x-slot name="title">ویرایش واحد - کد {{ $unit->id }}</x-slot>
    <x-slot name="body">
        <form action="{{ route('admin.units.update', $unit) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>نام:<span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control" name="name"  value="{{ old('name', $unit->name) }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>نماد:<span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control" name="symbol" value="{{ old('symbol', $unit->symbol) }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>دقت:<span class="text-danger">&starf;</span></label>
                        <input type="number" class="form-control" name="precision" value="{{ old('precision', $unit->precision) }}">
                    </div>
                </div>
				<div class="col-12">
					<div class="form-group">
					  <label class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" name="status" value="1" {{ old('status', $unit->status) == 1 ? 'checked' : null }}/>
						<span class="custom-control-label">وضعیت</span>
					  </label>
					</div>
				  </div>
            </div>
            <div class="modal-footer justify-content-center mt-2">
                <button class="btn btn-warning text-right item-right">به روزرسانی</button>
                <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
            </div>
        </form>
    </x-slot>
</x-modal>
@endforeach
