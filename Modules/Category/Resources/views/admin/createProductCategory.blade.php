<x-modal id="createProductCategory" size="md">
    <x-slot name="title">افزودن محصول</x-slot>
    <x-slot name="body">
        <form action="{{route('admin.category-product-sort.store')}}" method="post">
                @csrf
            <div class="modal-body">
                <input type="hidden" name="category_id" value="{{ $id }}">
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
            </div>
            <div class="modal-footer justify-content-center">
                <button  class="btn btn-primary  text-right item-right">ثبت</button>
                <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
            </div>
        </form>
    </x-slot>
</x-modal>