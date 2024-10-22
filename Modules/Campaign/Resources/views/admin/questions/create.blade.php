<x-modal id="createFaqModal" size="lg">
  <x-slot name="title">ثبت سوال کمپین</x-slot>
  <x-slot name="body">
  <form action="{{route('admin.campaignQuestions.store')}}" method="post">
      @csrf
      <style>
        .hidden{
          display: none
        }
      </style>
        <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
        <div class="modal-body">
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label class="control-label">سوال :<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="question"  placeholder="سوال را اینجا وارد کنید" value="{{ old('question') }}" required autofocus>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="questionType" class="control-label">نوع سوال :<span class="text-danger">&starf;</span></label>
                <select class="form-control" name="type" id="type" required>
                  <option selected disabled>- انتخاب کنید -</option>
                  <option value="options">یک مقداری</option>
                  <option value="checkbox">چند مقداری</option>
                  <option value="text">تشریحی</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row my-2 hidden" id="specification-values-section">
            <div class="col-12">
              <p class="header pr-2 font-weight-bold fs-22">گزینه ها را وارد کنید :</p>
            </div>
            <div class="col-12" id="specification-values-group">
              <div class="row" id="specification-values-group-row">
                <div class="col-12 d-flex plus-negative-container mt-2">
                  <button id="positive-btn-0" type="button" class="positive-btn btn btn-success btn-sm ml-1">+</button>
                  <button id="negative-btn-0" type="button" class="negative-btn btn btn-danger btn-sm ml-1" disabled>-</button>
                  <input id="value-0" name="data[0]" type="text" placeholder="مقدار" class="form-control mx-1">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer justify-content-center">
              <button  class="btn btn-primary  text-right item-right">ثبت</button>
              <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
          </div>
        </div>
    </form>
  </x-slot>
</x-modal>