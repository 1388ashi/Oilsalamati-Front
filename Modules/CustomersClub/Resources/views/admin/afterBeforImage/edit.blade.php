@foreach($afterBeforImages as $afterBeforImage)
<x-modal id="edit-image-{{$afterBeforImage['id']}}" size="md">
    <x-slot name="title">ویرایش تصویر قبل و بعد</x-slot>
    <x-slot name="body">
    <form action="{{route('admin.customersClub.approveBeforeAfterImages')}}" method="POST">
        @csrf
        <input type="hidden" name="before_after_id" value="{{ $afterBeforImage['id'] }}">
        <div class="modal-body">
            <div class="col-12">
                <div class="form-group">
                    <select class="form-control status2" required>
                        <option selected disabled>تایید شود</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer justify-content-center">
            <button type="submit" class="btn btn-warning text-right item-right">به روزرسانی</button>
            <button type="button" class="btn btn-outline-danger text-right item-right" data-dismiss="modal">برگشت</button>
        </div>
        </form>
    </x-slot>
</x-modal>
@endforeach
