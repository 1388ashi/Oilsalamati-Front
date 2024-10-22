
<x-modal id="createRecommendationModal" size="lg">
    <x-slot name="title">ثبت گروه جدید</x-slot>
    <x-slot name="body">
        <form action="{{ route('admin.recommendations.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-xl-6 form-group">
                        <label class="control-label">گروه را انتخاب یا وارد کنید (انگلیسی):</label><span class="text-danger">&starf;</span>
                        <select class="form-control" name="group_name">
                            <option value="">انتخاب کنید...</option>
                            @foreach ($existsGroupNames as $existsGroupNames)
                                <option value="{{ $existsGroupNames->group_name }}"
                                    {{ $existsGroupNames->group_name == old('group_name') ? 'selected' : '' }}>
                                    {{ $existsGroupNames->group_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-xl-6 form-group">
                        <label class="control-label">عنوان:</label>
                        <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                    </div>
                    <div class="col-12 col-xl-6 form-group">
                        <label class="control-label">نوع لینک :</label><span class="text-danger">&starf;</span>
                        <select id="linkableType" onchange="toggleInput()" name="linkable_type"
                            class="form-control">
                            <option value="self_link" class="custom-menu">لینک دلخواه</option>
                            @foreach ($linkables as $link)
                                <option value="{{ $link['unique_type'] }}" class="model">{{ $link['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-xl-6 form-group" id="divLinkable" style="display: none">
                        <label class="control-label">آیتم های لینک :</label>
                        <select id="linkableId" name="linkable_id" class="form-control select2">
                            <option value="" class="custom-menu">انتخاب</option>
                        </select>
                    </div>
                    <div class="col-12 col-xl-6 form-group" id="divLink" style="display: none">
                        <label class="control-label">لینک دلخواه :</label>
                        <input type="text" id="link" name="link" class="form-control">
                    </div>
                    <div class="col-12 col-xl-6 form-group">
                        <label class="control-label">بنر دسکتاپ :</label>
                        <input type="file" name="images[]" class="form-control" multiple="multiple">
                    </div>
                    <div class="col-12 col-xl-6 form-group">
                        <label class="control-label">بنر موبایل :</label>
                        <input type="file" name="images_mobile[]" class="form-control" multiple="multiple">
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="label" class="control-label"> وضعیت: </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', 1) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">فعال</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100 d-flex justify-content-center" style="gap: 10px;">
                <button class="btn btn-primary">ثبت</button>
                <button class="btn btn-outline-danger" data-dismiss="modal">برگشت</button>
            </div>
        </form>
    </x-slot>
</x-modal>
