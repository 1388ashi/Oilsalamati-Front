<x-modal id="createImageModal" size="md">
    <x-slot name="title">ثبت تصویر قبل و بعد برای مشتری</x-slot>
    <x-slot name="body">
    <form action="{{route('admin.customersClub.setBeforeAfterImages')}}" method="post"  class="save" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="col-12">
                <div class="form-group">
                    <label class="control-label">انتخاب مشتری :<span class="text-danger">&starf;</span></label>
                    <select class="form-control search-customer-ajax"  id="customer-selection" name="customer_id">

                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label class="control-label">انتخاب محصول :<span class="text-danger">&starf;</span></label>
                    <select class="form-control select2" id="filter-products" name="product_id">
                        <option value="" selected>انتخاب</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : null }}>{{ $product->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="before_image" class="control-label"> تصویر قبل از استفاده از محصول: <span class="text-danger">&starf;</span></label>
                    <input type="file" id="before_image" class="form-control" name="before_image" value="{{ old('before_image') }}">
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="after_image" class="control-label"> تصویر بعد از استفاده از محصول: <span class="text-danger">&starf;</span></label>
                    <input type="file" id="after_image" class="form-control" name="after_image" value="{{ old('after_image') }}">
                </div>
            </div>
        </div>
        <div class="modal-footer justify-content-center">
            <button  class="btn btn-primary  text-right item-right">ثبت</button>
            <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
        </div>
    </form>
</x-slot>
</x-modal>