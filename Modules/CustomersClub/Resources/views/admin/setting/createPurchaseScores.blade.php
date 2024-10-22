<x-modal id="createScoreModal" size="md">
    <x-slot name="title">افزودن امتیاز خرید جدید</x-slot>
    <x-slot name="body">
        <form action="{{route('admin.customersClub.addPurchaseScore')}}" method="post">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="control-label">عنوان :<span class="text-danger">&starf;</span></label>
                            <input
                            type="text"
                            id="title"
                            class="form-control"
                            name="title"
                            placeholder="عنوان را وارد کنید"
                            value="{{ old('title') }}"
                            required
                            autofocus
                            />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="control-label">حداقل مبلغ خرید :<span class="text-danger">&starf;</span></label>
                            <input
                            type="text"
                            id="min_value"
                            class="form-control comma"
                            name="min_value"
                            placeholder="حداقل مبلغ را وارد کنید"
                            value="{{ old('min_value') }}"
                            required
                            autofocus
                            />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="control-label">حداکثر مبلغ خرید :<span class="text-danger">&starf;</span></label>
                            <input
                            type="text"
                            id="max_value"
                            class="form-control comma"
                            name="max_value"
                            placeholder="حداکثر مبلغ را وارد کنید"
                            value="{{ old('max_value') }}"
                            required
                            autofocus
                            />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="control-label">مقدار بن :<span class="text-danger">&starf;</span></label>
                            <input
                            type="text"
                            id="bon_value"
                            class="form-control comma"
                            name="bon_value"
                            placeholder="مقدار بن را وارد کنید"
                            value="{{ old('bon_value') }}"
                            required
                            autofocus
                            />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="control-label">مقدار امتیاز :<span class="text-danger">&starf;</span></label>
                            <input
                            type="text"
                            id="score_value"
                            class="form-control comma"
                            name="score_value"
                            placeholder="مقدار امتیاز را وارد کنید"
                            value="{{ old('score_value') }}"
                            required
                            autofocus
                            />
                        </div>
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