@foreach ($shippingRanges as $range)
<x-modal id="edit-range-modal-{{ $range->id }}" size="md">
    <x-slot name="title">ایجاد بازه جدید</x-slot>
    <x-slot name="body">
        <form action="{{ route('admin.shipping-ranges.update', $range) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label> از : <span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control comma" name="lower" value="{{ old('lower', number_format($range->lower)) }}" required />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label> تا : <span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control comma" name="higher" value="{{ old('higher', number_format($range->higher)) }}" required />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label> مبلغ (تومان) : <span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control comma" name="amount" value="{{ old('amount', number_format($range->amount)) }}" required />
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-warning" type="submit">بروزرسانی</button>
                <button class="btn btn-outline-danger" data-dismiss="modal">بستن</button>
              </div>
        </form>
    </x-slot>
</x-modal>
@endforeach
