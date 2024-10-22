<x-modal id="showAsnwserModal" size="md">
  <x-slot name="title">پاسخ به سوال کاربر</x-slot>
  <x-slot name="body">
    <form action="{{ route('admin.contacts.answer') }}" method="post">
      @csrf
      @method('PATCH')
      <div class="form-group">
        <div id="answer"></div>
      </div>
      <div class="modal-footer" style="direction: ltr">
        <button class="btn btn-success text-right item-right" type="submit">ثبت پاسخ</button>
      </div>
    </form>
  </x-slot>
</x-modal>
