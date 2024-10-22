@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item"><a href="{{ route('admin.recommendations.index') }}"><i
                        class="fe fe-home ml-1"></i>لیست محصولات پیشنهادی</a></li>
            <li class="breadcrumb-item active">ویرایش محصول پیشنهادی</li>
        </ol>
    </div>
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title">ویرایش محصول پیشنهادی</p>
            <x-card-options />
        </div>
        <div class="card-body">
            @include('components.errors')
            <form action="{{ route('admin.recommendations.update', $recommendation) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-6 form-group">
                        <label class="control-label">گروه را انتخاب یا وارد کنید
                            (انگلیسی):</label><span class="text-danger">&starf;</span>
                        <input type="text" class="form-control" name="group_name"
                            value="{{ $recommendation->group_name }}" readonly>
                    </div>
                    <div class="col-6 form-group">
                        <label class="control-label">عنوان:</label>
                        <input type="text" class="form-control" name="title" placeholder="عنوان را اینجا وارد کنید"
                            value="{{ old('title', $recommendation->title) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 form-group">
                        <label class="control-label">نوع لینک :</label><span class="text-danger">&starf;</span>
                        <select name="linkable_type" onchange="toggleEditInput(this.value,{{ $recommendation->id }})"
                            class="form-control" id="typeLink-{{ $recommendation->id }}">
                            @foreach ($linkables as $link)
                                <option class="model" value="{{ $link['unique_type'] }}"
                                    @if ($link['linkable_type'] == $recommendation->linkable_type) selected @endif>{{ $link['label'] }}
                                </option>
                            @endforeach
                            <option value="self_link2" @if ($recommendation->link) selected @endif
                                class="custom-menu">لینک دلخواه</option>
                        </select>
                    </div>
                    <div class="col-4" id="divLinkableEditId-{{ $recommendation->id }}">
                        <div class="form-group">
                            <label class="control-label">آیتم های لینک :</label>
                            <select name="linkable_id" id="linkableEditId-{{ $recommendation->id }}"
                                class="form-control select2" disabled>
                                <option class="custom-menu">انتخاب</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4 form-group">
                        <label class="control-label">لینک دلخواه :</label>
                        <input id="linkEdit-{{ $recommendation->id }}" type="text" name="link" class="form-control"
                            value="{{ old('link', $recommendation->link) }}" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 form-group">
                        <label class="control-label">بنر دسکتاپ :</label>
                        <input type="file" name="images[]" class="form-control" multiple="multiple">
                    </div>
                    <div class="col-4 form-group">
                        <label class="control-label">بنر موبایل :</label>
                        <input type="file" name="images_mobile[]" class="form-control" multiple="multiple">
                    </div>
                    <div class="col-4">
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
                <div class="row mt-3">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">ویرایش و ذخیره</button>
                            <a class="btn btn-outline-danger" href="{{ route('admin.recommendations.index') }}">برگشت</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- row opened -->
    @if ($recommendation->images_showcase)
        <div class="row">
            <div class="col-xl-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title font-weight-bold font-size-20">بنر های دسکتاپ</p>
                        <div class="product-slider">
                            <div class="product-carousel">
                                <div id="carousel" class="carousel slide" data-ride="false">
                                    <div class="carousel-inner">
                                        <div class="carousel-item active d-flex">
                                            @foreach ($recommendation->images_showcase['images'] ?? [] as $item)
                                                <div class="thumb my-2" style="margin-left: 10px;">
                                                    <a href="{{ $item->url }}" target="_blank">
                                                        <img src="{{ $item->url }}"
                                                            style="height: 185px; width: auto;" class="img-fluid"
                                                            alt="{{ $item->url }}">
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($recommendation->images_showcase['images_mobile']))
                <div class="col-xl-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title font-weight-bold font-size-20">بنر های موبایلی</p>
                            <div class="product-slider">
                                <div class="product-carousel">
                                    <div id="carousel" class="carousel slide" data-ride="false">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active  d-flex">
                                                @foreach ($recommendation->images_showcase['images_mobile'] ?? [] as $item)
                                                    <div class="thumb my-2 active" style="margin-left: 10px;">
                                                        <a href="{{ $item->url }}" target="_blanck">
                                                            <img src="{{ $item->url }}"
                                                                style="height: 185px;width: auto" class="img-fluid"
                                                                alt="{{ $item->url }}">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif
    <!-- row closed -->
@endsection
@section('scripts')
    <script>
        var linkables = @json($linkables);

        function toggleEditInput(recommendation, id) {
            let selectOption2 = $(`#typeLink-${id}`).val();
            let linkInput = $(`#linkEdit-${id}`);
            $(`#linkableEditId-${id}`).attr('disabled', 'disabled');
            linkInput.value = '';
            linkInput.disabled = true;
            if (selectOption2 == "self_link2") {
                linkInput.removeAttr('disabled');
            } else {

                linkInput.attr("disabled", "disabled");
                $(`#linkableEditId-${id}`).removeAttr('disabled');
                let linkables = @json($linkables);
                let linkableId = document.getElementById(`linkableEditId-${id}`);
                linkableId.innerHTML = '';

                let findedItem = linkables.find(linkable => {

                    return linkable.unique_type == $(`#typeLink-${id}`).val();
                });

                if (findedItem) {
                    let option = '';
                    if (findedItem.models != null) {
                        findedItem.models.forEach(model => {
                            let title = model.title ?? (model.name ?? 'ندارد');
                            option += `<option value="${model.id}">${title}</option>`;
                        });
                        linkableId.innerHTML = option;
                    } else {
                        linkableId.innerHTML = `<option value="" selected disabled>آیتمی وجود ندارد</option>`;
                    }
                }
            }
        }
    </script>
@endsection
