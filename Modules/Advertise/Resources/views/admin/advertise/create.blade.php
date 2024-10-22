<x-modal id="createAdvertisementsModal" size="md">
    <x-slot name="title">ثبت برند</x-slot>
    <x-slot name="body">
        <form action="{{route('admin.advertise.store')}}" method="post" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">لینک:<span class="text-danger">&starf;</span></label>
                    <input type="text" class="form-control" name="link"  placeholder="لینک را اینجا وارد کنید" value="{{ old('link') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label class="control-label">تصویر:<span class="text-danger">&starf;</span></label>
                    <input  class="form-control" type="file" name="image">
                </div>
                <input type="hidden" name="position_id" value="{{$position->id}}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="label" class="control-label"> تب جدید: </label>
                            <label class="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                name="new_tab"
                                value="1"
                                {{ old('new_tab ', 1) == 1 ? 'checked' : null }}
                            />
                            <span class="custom-control-label">فعال</span>
                            </label>
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