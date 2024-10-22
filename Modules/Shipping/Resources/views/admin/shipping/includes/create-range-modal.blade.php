 <x-modal id="create-range-modal" size="md">
    <x-slot name="title">ایجاد بازه جدید</x-slot>
    <x-slot name="body">
        <form action="{{ route('admin.shipping-ranges.store') }}" method="POST">
            @csrf
            <input type="hidden" name="shipping_id" value="{{ $shipping->id }}">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label> از : <span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control comma" name="lower" value="{{ old('lower') }}" required />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label> تا : <span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control comma" name="higher" value="{{ old('higher') }}" required />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label> مبلغ (تومان) : <span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control comma" name="amount" value="{{ old('amount') }}" required />
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button class="btn btn-primary" type="submit">ثبت و ذخیره</button>
               <button class="btn btn-outline-danger" data-dismiss="modal">بستن</button>
             </div>
        </form>
    </x-slot>
</x-modal>