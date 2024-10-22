@foreach($sizeChartTypes as $sizeChartType)
    <div class="modal fade mt-5" tabindex="-1" id="edit-sizeChartType-{{ $sizeChartType->id }}" role="dialog"
        aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('admin.sizecharttype.update', $sizeChartType->id)}}" method="POST">
                    @csrf
                    @method('PATCH')

                <div class="modal-header">
                    <p class="modal-title font-weight-bolder">ویرایش نوع چارت</p>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                      <label class="control-label">نام:<span class="text-danger">&starf;</span></label>
                      <input type="text" class="form-control" name="name"  placeholder="عنوان را اینجا وارد کنید" value="{{ old('name', $sizeChartType->name) }}"  autofocus>
                    </div>
                    <div class="form-group mr-3" id="addType">
                      @foreach ($sizeChartType->values as $item)
                      <div class="input-container row">
                        <button width class="btn btn-success btn-sm btn-icon neg addPurchaseItemButton2 ml-1 mb-1" type="button">+</button>
                        <button  class="btn btn-danger btn-sm btn-icon neg deleteRow ml-1 mb-1" type="button">-</button>
                        <input type="text" style="width: 85%" class="numberInput form-control mb-2" placeholder="نوع" name="values[]" value="{{$item->name}}"/>
                      </div>
                      @endforeach
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button  class="btn btn-warning text-right item-right">به روزرسانی</button>
                    <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
