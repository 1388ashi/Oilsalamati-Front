@foreach ($postCategories as $category)
	<div class="modal fade" id="editPostCategoryModal-{{ $category->id }}" style="display: none;" aria-hidden="true">
		<div class="modal-dialog modal-md" role="document">
			<div class="modal-content modal-content-demo">
				<div class="modal-header">
					<p class="modal-title" style="font-size: 20px;">ویرایش دسته بندی کد - {{ $category->id }}</p>
					<button aria-label="Close" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
				</div>
				<div class="modal-body">
					<form action="{{ route('admin.post-categories.update', $category) }}" method="post" class="save">
						@csrf
						@method('PATCH')
						<div class="row">

							<div class="col-12">
								<div class="form-group">
									<label for="name" class="control-label">نام سدته بندی :<span class="text-danger">&starf;</span></label>
									<input type="text" id="name" class="form-control" name="name" required value="{{ old('name', $category->name) }}">
								</div>
							</div>

							<div class="col-12">
								<div class="form-group">
									<label for="label" class="control-label"> وضعیت: </label>
									<label class="custom-control custom-checkbox">
										<input
											type="checkbox"
											class="custom-control-input"
											name="status"
											id="status"
											value="1"
											{{ old('status', $category->status) == 1 ? 'checked' : null }}
										/>
										<span class="custom-control-label">فعال</span>
									</label>
								</div>
							</div>

						</div>

						<div class="modal-footer justify-content-center">
							<button class="btn btn-warning" type="submit">بروزرسانی</button>
							<button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
@endforeach
