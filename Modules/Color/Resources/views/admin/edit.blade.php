@foreach($colors as $color)
  <x-modal id="edit-color-{{ $color->id }}" size="md">
    <x-slot name="title">ویرایش رنگ - کد {{ $color->id }}</x-slot>
    <x-slot name="body">
      <form action="{{route('admin.colors.update', $color->id)}}" method="POST">
				@csrf
				@method('patch')
				<div class="row">
					<div class="col-12">
						<div class="form-group">
							<label>عنوان :<span class="text-danger">&starf;</span></label>
							<input type="text" class="form-control" name="name" value="{{ old('name', $color->name) }}" required autofocus>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label>انتخاب رنگ:<span class="text-danger">&starf;</span></label>
							<input type="color" class="form-control" name="code" value="{{$color->code}}" onchange="updateColor(this.value)">
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input" name="status" value="1" {{ old('status', $color->status) == 1 ? 'checked' : null }}/>
								<span class="custom-control-label">وضعیت</span>
							</label>
						</div>
					</div>
				</div>
				<div class="modal-footer justify-content-center">
					<button class="btn btn-warning text-right item-right">به روزرسانی</button>
					<button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
				</div>
			</form>
    </x-slot>
  </x-modal>
@endforeach
