@foreach($sliders as $slider)
<x-modal id="edit-slider-{{ $slider->id }}" size="md">
    <x-slot name="title">ویرایش اسلایدر</x-slot>
    <x-slot name="body">
        <form action="{{route('admin.sliders.update', [$slider->id])}}" method="POST" class="save" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <input type="hidden" name="group" value="{{$group}}">

            <div class="form-group">
            <label class="control-label">عنوان :<span class="text-danger">&starf;</span></label>
            <input type="text" class="form-control" name="title"  placeholder="عنوان را اینجا وارد کنید" value="{{ old('title',$slider->title) }}" required autofocus>
            </div>
            <div class="form-group">
            <label class="control-label">توضیحات:<span class="text-danger">&starf;</span></label>
            <textarea name="description" class="form-control"  cols="70" rows="3">{{old('description',$slider->description)}}</textarea>
            </div>
            <div class="form-group">
            <label for="image" class="control-label"> تصویر:</label>
            <input type="file" id="image" class="form-control" name="image" value="{{ old('image') }}">
            </div>
            <div class="img-holder mt-1 img-show " style="width: auto;height: auto">
            <img style="height: auto;width: 100%;" src="{{ optional($slider->getMedia('main')->first())->getUrl() }}">
            </div>
            <div class="row">
            <div class="col-12 form-group">
                <label class="control-label">نوع لینک :</label><span class="text-danger">&starf;</span>
                <select name="linkable_type"
                    onchange="toggleEditInput(this.value,{{$slider->id}})"
                    class="form-control" id="typeLink-{{$slider->id}}">
                    @foreach ($linkables as $link)
                        <option  class="model" value="{{ $link['unique_type'] }}" @if ($link['linkable_type'] == $slider->linkable_type) selected @endif>{{ $link['label'] }}</option>
                    @endforeach
                    <option value="self_link2" @if ($slider->link) selected @endif class="custom-menu">لینک دلخواه</option>
                </select>
            </div>
            <div class="col-12 form-group " id="divLinkableEditId-{{$slider->id}}" style="display: none">
                <label class="control-label">آیتم های لینک :</label>
                <select name="linkable_id" id="linkableEditId-{{$slider->id}}" class="form-control select2">
                    <option class="custom-menu">انتخاب</option>
                </select>
            </div>
            <div class="col-12 form-group">
                <label class="control-label">لینک دلخواه :</label>
                <input id="linkEdit-{{$slider->id}}" type="text" name="link" class="form-control" value="{{ old('link', $slider->link) }}" disabled>
            </div>
        </div>
                <div class="col-md-4">
                    <div class="form-group">
                    <label for="label" class="control-label"> وضعیت: </label>
                    <label class="custom-control custom-checkbox">
                        <input
                        type="checkbox"
                        class="custom-control-input"
                        name="status"
                        id="status"
                        value="1"
                        {{ old('status', $slider->status) == 1 ? 'checked' : null }}
                        />
                        <span class="custom-control-label">فعال</span>
                    </label>
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
