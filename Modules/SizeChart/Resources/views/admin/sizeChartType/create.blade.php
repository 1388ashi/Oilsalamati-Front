<div class="modal fade"  id="addsizeChartType">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <form action="{{route('admin.sizecharttype.store')}}" method="post">
              @csrf
            <div class="modal-header">
              <p class="modal-title font-weight-bolder">ثبت نوع جدید</p>
              <button  class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
              </button>
            </div>
          <div class="modal-body">
              <div class="form-group">
                  <label class="control-label">نام:<span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control" name="name"  placeholder="عنوان را اینجا وارد کنید" value="{{ old('name') }}"  autofocus>
              </div>
              <div class="form-group mr-3" id="addType">
                <div class="input-container row">
                  <button class="btn btn-success btn-sm btn-icon  neg addPurchaseItemBtn ml-1 mb-1" type="button">+</button>
                    <input type="text" style="width: 91%" class="numberInput form-control mb-2" placeholder="نوع" name="values[]" />
                </div>
              </div>
          </div>
          <div class="modal-footer justify-content-center">
              <button  class="btn btn-primary  text-right item-right">ثبت</button>
              <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
          </div>
      </form>
      </div>
  </div>
</div>
