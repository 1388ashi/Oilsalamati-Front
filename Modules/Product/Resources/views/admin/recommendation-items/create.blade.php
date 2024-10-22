<x-modal id="createItemsModal" size="md">
    <x-slot name="title">ثبت آیتم جدید</x-slot>
    <x-slot name="body">
            <form action="{{route('admin.recommendation-items.store',$recommendation->id)}}" method="post"  class="save">
                @csrf
            <div class="modal-body">
                <input type="hidden" name="recommendation_id" value="{{$recommendation->id}}">
                <div class="col-12">
                    <div class="form-group">
                        <label class="control-label">انتخاب محصول :<span class="text-danger">&starf;</span></label>
                        <select class="form-control select2" id="filter-products" name="products[]" multiple>
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