@foreach($list as $item)
<x-modal id="edit-exchangeBon-{{$item->id}}" size="md">
    <x-slot name="title">ویرایش درخواست تبدیل بن</x-slot>
    <x-slot name="body">
        <form action="{{route('admin.customersClub.updateBonConvertRequest')}}" method="POST">
            @csrf
        <div class="modal-body">
            <div class="col-12">
                <div class="form-group">
                    <label class="control-label">توضیحات :</label>
                    <textarea name="description" class="form-control" cols="70" rows="3">{{old('description',$item->description)}}</textarea>
                </div>
            </div>
            <input type="hidden" name="id" value="{{$item->id}}">
            <div class="col-12">
                <div class="form-group">
                    <label class="control-label">وضعیت درخواست :<span class="text-danger">&starf;</span></label>
                    <select class="form-control " name="status">
                        <option value="" selected>- انتخاب کنید -</option>
                        <option value="approved" @if ($item->status == 'approved') selected @endif>تایید شود</option>
                        <option value="rejected"@if ($item->status == 'rejected') selected @endif>حذف شود</option>
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
