@extends('admin.layouts.master')
@section('styles')
  <style>
    .bg-white{
      border-radius: 20px;
    }
    .card-header{
      border-bottom: 0 ;
    }
    .span-box{
      margin-bottom: 25px;
    }


    /*upload image*/

    #preview-container {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .preview-image {
        position: relative;
        margin: 10px;
    }

    .preview-image img {
        max-width: 150px;
        max-height: 150px;
        object-fit: cover;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .delete-button {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    /*//upload image*/

  </style>
@endsection
@section('content')
<div class="page-header">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-life-buoy ml-1"></i> داشبورد</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.products.index') }}">لیست محصولات</a></li>
    <li class="breadcrumb-item active" aria-current="page">ویرایش محصول</li>
  </ol>
</div>
{{-- <div class="page-header">
  @php($items = [['title' => 'لیست محصولات', 'route_link' => 'admin.products.index'],['title' => 'ویرایش محصول']])
  <x-breadcrumb :items="$items"/>
</div> --}}


  @include('components.errors')
  <form action="{{ route('admin.products.store') }}" method="post" class="save"
        id="companyForm" enctype="multipart/form-data">
      @csrf
    <!-- row opened -->
    <div class="row">
      <div class="col-xl-8 col-sm-12">
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">اطلاعات محصول</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="tab-content">
                  <div class="tab-pane active" id="tab-1">
                    <div class="profile-log-switch">
                      <!-- Row-->
                      <div class="row">
                        <div class="col-xl-12">
                          <div class="">
                            <div class="card mb-0 p-2 box-shadow-0">
                              <div class="row">
                                <div class="col-12 form-group">
                                  <label for="title" >نام محصول</label>
                                  <span class="text-danger">*</span>
                                  <input type="text" class="form-control" name="product[title]" placeholder="لطفا نام محصول را وارد کنید" value="{{ old('product.title') }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="title" >موجودی</label>
                                  <input type="text" class="form-control" name="product[quantity]" placeholder="لطفا موجودی محصول را وارد کنید" value="{{ old('product.quantity') }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="title" >بارکد</label>
                                  <input type="text" class="form-control" name="product[barcode]" placeholder="لطفا بارکد محصول را وارد کنید" value="{{ old('product.barcode') }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="sku" >SKU</label>
                                  <input type="text" class="form-control" name="product[SKU]" placeholder="لطفا sku محصول را وارد کنید" value="{{ old('product.SKU') }}">
                                </div>
                                <!--دسته بندی-->
                                <div class="col-12 form-group">
                                  <label class="form-label" style="display:inline;">دسته بندی</label>
                                  <span class="text-danger" style="display:inline;">&starf;</span>
                                  <select class="form-control custom-select select2 category-multiple" multiple="multiple" name="product[categories][]" data-placeholder="دسته بندی را انتخاب کنید ...">
                                    <option value="">دسته بندی را انتخاب کنید</option>
                                    @foreach($categories as $category)
                                      <option value="{{ $category->id }}">{{ $category->title }}</option>
                                      <!-- show childs -->
                                      @if($category->children)
                                        @foreach($category->children as $child)
                                          <option value="{{ $child->id }}">
                                            @if($child->parent_id)
                                              -
                                            @endif
                                            {{ $child->title }}</option>
                                        @endforeach
                                      @endif
                                    @endforeach
                                  </select>
                                </div>

                                <!--برند-->
                                <!--واحد-->
                                <div class="col-12 form-group">
                                  <label for="title" >واحد</label>
                                  <select class="form-select form-control" name="product[unit_id]" aria-label="واحد" >
                                    {{--                                  <option selected>لطفا واحد را انتخاب کنید</option>--}}
                                    @foreach($units as $unit)
                                      <option value="{{$unit->id}}">{{$unit->name}}</option>
                                    @endforeach
                                  </select>
                                </div>

                                <div class="col-12 form-group">
                                  <label for="title" >وزن</label>
                                  <input type="text" min="1" class="form-control" name="product[weight]" placeholder="لطفا وزن محصول را وارد کنید" value="{{ old('product.weight') }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="max_limit" >حداکثر تعداد خرید</label>
                                  <input type="number" min="1" class="form-control" name="product[max_limit]" placeholder="لطفا حداکثر تعداد خرید را وارد کنید" value="{{ old('product.max_limit') }}">
                                </div>
                                <!--تگ ها-->
                                <div class="col-12 form-group">
                                  <label class="form-label" style="display:inline;">تگ ها</label>
                                  <span class="text-danger" style="display:inline;">&starf;</span>
                                  <select class="form-control custom-select select2 tag-multiple" multiple="multiple" name="product[tags[]]" data-placeholder="تگ ها را انتخاب کنید ...">
                                    <option value="">تگ را انتخاب کنید</option>
                                    @foreach($tags as $tag)
                                      <option value="{{ $tag->id }}">
                                        {{ $tag->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>

                              <!-- update button was here-->

                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- End Row -->
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">توضیحات</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                @include('components.editor',['name' => 'product[text]','required' => 'true','field_name' => 'text'])
              </div>
            </div>
          </div>
        </div>
        {{-- <div class="bg-white widget-user mb-5">
          {{-- <div class="card">
            <div class="card-header">
              <h1 class="card-title">تنوع ها</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                  <div class="col-12 form-group">
                      <label class="form-label" style="display:inline;">رنگ ها</label>
                      <span class="text-danger" style="display:inline;">&starf;</span>
                      <select class="form-control custom-select select2 color-multiple" multiple="multiple" name="colors[]" data-placeholder="رنگ ها را انتخاب کنید ...">
                          <option value="">رنگ را انتخاب کنید</option>
                          @foreach($colors as $color)
                              <option value="{{ $color->id }}" data-color="{{ $color->code }}">
                                  {{ $color->name }}
                              </option>
                          @endforeach
                      </select>
                  </div>
                  <div class="col-12 form-group">
                      <label class="form-label" style="display:inline;">ویژگی ها</label>
                      <span class="text-danger" style="display:inline;">&starf;</span>
                      <select class="form-control custom-select select2 attribute-multiple" multiple="multiple" name="attributes[]" data-placeholder="ویژگی ها را انتخاب کنید ...">
                          <option value="">ویژگی را انتخاب کنید</option>
                          @foreach($attributes as $attribute)
                              <option value="{{ $attribute->id }}" data-color="{{ $attribute->code }}">
                                  {{ $attribute->label }}
                              </option>
                          @endforeach
                      </select>
                  </div>

                  <div class="col-12 form-group">
                      <label class="form-label" style="display:inline;">مقادیر ویژگی روغن</label>
                      <span class="text-danger" style="display:inline;">&starf;</span>
                      <select class="form-control custom-select select2 attribute-multiple" multiple="multiple" name="colors[]" data-placeholder="ویژگی ها را انتخاب کنید ...">
                          <option value="">ویژگی را انتخاب کنید</option>
{{--                          todo:implement--}}

                      {{-- </select>
                  </div>

                  <div class="col-12">
                      <table class="table">
                          <thead>
                          <tr>
                              <th scope="col">عنوان</th>
                              <th scope="col">قیمت</th>
                              <th scope="col">وزن</th>
                              <th scope="col">حداکثر تعداد خرید</th>
                              <th scope="col">موجودی</th>
                              <th scope="col">تنوع مرجع</th>
                              <th scope="col">عملیات</th>
                          </tr>
                          </thead>
                          <tbody>
                          <tr>
                              <th scope="row">1</th>
                              <td>Mark</td>
                              <td>Otto</td>
                              <td>@mdo</td>
                              <td>@mdo</td>
                              <td>@mdo</td>
                              <td>@mdo</td>
                          </tr>
                          </tbody>
                      </table>

                  </div>

              </div>
            </div>
          </div>

        </div> --}}
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">عکس ها</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                  <div class="upload-container">
                      <input type="file" id="image-input" name="product[images][]" class="form-control" multiple accept="image/*">
                      <div id="preview-container"></div>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">مشخصات محصول</h1>
            </div>
            <div class="card-body">
              <div class="border-0">

                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">نام</th>
                      <th scope="col">مقدار</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php($specification_ids = array())
                    @foreach($specifications as $i => $specification)
                    <input type="hidden" name="product[specifications][{{ $i }}][id]" value="{{ $specification->id }}">

                    <tr>
                    <th>{{ $specification->label }}</th>
                    <td>
                        @if($specification->type == 'text')
{{--                            1--}}
                                <input type="text" value="" name="product[specifications][{{$i}}][value]" class="form-control" aria-label="Username" aria-describedby="basic-addon1">

                        @elseif($specification->type == 'select')
{{--2--}}
                          <select class="form-control" id="exampleFormControlSelect1" name="product[specifications][{{$i}}][value]">
                            <option value="" selected>  - انتخاب کنید -</option>
                            @foreach($specification->values as $specificationValue)
                            @if(in_array($specificationValue->id,$specification_ids) == false)
                                        <option value="{{$specificationValue->id}}">{{ $specificationValue->value }}</option>
                                @endif
                            @endforeach
                              {{-- @foreach($product->specifications as $productSpecification)
                              @if ($productSpecification->type == 'select')
                                  @if($productSpecification->id == $specification->id)
                                      @foreach ($productSpecification->values as $productSpecificationVal)
                                          <option value="{{$productSpecificationVal->id}}" selected>{{$productSpecificationVal->value}}</option>
                                      @endforeach
                                  @else
                                  @foreach ($specification->values as $specificationVal)

                                  @endforeach
                                      <option value="{{$specificationVal->id}}">{{$specificationVal->value}}</option>
                                  @endif
                              @endif
                              @endforeach --}}
                            {{-- @if($flag==true)
                              @foreach($specification->values as $specificationValue)
                                  @if(in_array($specificationValue->id,$specification_ids) == false)
                                          <option>{{ $specificationValue->value }}</option>
                                  @endif
                              @endforeach
                            @endif --}}
                        </select>
                        @elseif($specification->type == 'multi_select')
                            <select class="form-control custom-select select2 tag-multiple multiple" multiple="multiple" name="product[specifications][{{$i}}][value][]" data-placeholder="انتخاب مقدار ...">
                                @foreach($specification->values as $item)
                                    <option value="{{ $item->id }}"
                                    {{-- @foreach($product->specifications as $productSpecification)
                                          @if($productSpecification->id == $specification->id)
                                              @foreach($productSpecification->pivot->specificationValues as $value)
                                                @if($value->id == $item->id)
                                                    {{ 'selected' }}
                                                  @endif
                                              @endforeach
                                          @endif
                                      @endforeach --}}
                                    >
                                        {{ $item->value }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </td>
                  </tr>
                @endforeach
                  </tbody>
                </table>

              </div>
            </div>
          </div>
        </div>
{{--        <div class="bg-white widget-user mb-5">--}}
{{--          <div class="card">--}}
{{--            <div class="card-header">--}}
{{--              <h1 class="card-title">سایز چارت</h1>--}}
{{--            </div>--}}
{{--            <div class="card-body">--}}
{{--              <div class="border-0">--}}

{{--              </div>--}}
{{--            </div>--}}
{{--          </div>--}}
{{--        </div>--}}

        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">اطلاعات سئو</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label for="meta_title" class="control-label">تیتر متا</label>
                      <input type="text" name="meta_title" value="{{ old('product.meta_title') }}"
                             class="form-control" id="meta_title"
                             placeholder="تیتر متا را اینجا وارد کنید"  autofocus>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-group">
                      <label for="meta_description" class="control-label">توضیحات متا</label>
                      <textarea name="product[meta_description]"  class="form-control" id="meta_description"
                                rows="3" cols="30" placeholder="توضیحات متا را اینجا وارد کنید"
                                autofocus>{{ old('product.meta_description') }}</textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>
      <div class="col-xl-4 col-sm-12">
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header row">
              <div class="col align-self-start">
                <h4 class="card-title text-bold">انتشار</h4>
              </div>
              <div class="col align-self-center">
                &nbsp;
              </div>
              <div class="col align-self-end">
                <button class="btn btn-primary" type="submit">ثبت و ذخیره</button>
              </div>

            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="text-right">
                  <div class="form-group col-12">
                    <label for="status">وضعیت</label>
                    <select class="form-control form-select-sm" name="product[status] " aria-label=".form-select-sm example">
                      @foreach(\Modules\Product\Entities\Product::getAvailableStatuses() as $status)
                        <option value="{{$status}}">
                          {{\Modules\Product\Entities\Product::getStatusLabelAttribute($status)}}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-12">
                    <span>زمانبندی کردن انتشار</span>
                    <input type="checkbox" class="" name="status">
                      <br>
                      <br>
                    <!---published_at should put here--->
                      <div class="col">
                          <div class="form-group">
                              <label for="status">تاریخ انتشار</label>
                              <div class="input-group">
                                  <div class="input-group-prepend">
                                      <div class="input-group-text">
                                          <i class="feather feather-calendar"></i>
                                      </div>
                                  </div>
                                  <input class="form-control fc-datepicker" id="published_at_show"
                                          value="{{ old('product.published_at') }}"
                                          placeholder="تاریخ انتشار..." type="text">
                                  <input hidden name="product[published_at]" id="published_at" type="text">
                              </div>
                          </div>
                      </div>

                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">قیمت گذاری</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="form-group">
                  <label for="unit_price" class="control-label">قیمت واحد</label>
                  <span class="text-danger">&starf;</span>
                  <input type="text" name="product[unit_price]" value="{{ old('product.unit_price') }}"
                    class="form-control comma" min="0"
                    placeholder="قیمت واحد را اینجا وارد کنید" required autofocus>
                </div>
                <div class="form-group">
                  <label for="discount" class="control-label">تخفیف</label>
                  <input type="text" name="product[discount]" value="{{ old('product.discount') }}"
                    class="form-control comma" min="0"
                    placeholder="تخفیف را اینجا وارد کنید"  autofocus>
                </div>


              </div>
            </div>
          </div>
        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">تنظیمات نمایش</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="form-group col-12 span-box">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[chargeable]" value="1" {{ old('product.chargeable') == 1 ? 'checked' : null }} checked>
                    <span class="custom-control-label">قابل شارژ<span>
                  </label>
                </div>

                <div class="form-group col-12 span-box">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[new_product_in_home]" value="0" {{ old('product.new_product_in_home') == 1 ? 'checked' : null }} />
                    <span class="custom-control-label">نمایش در لیست محصولات جدید<span>
                  </label>
                </div>

                <div class="form-group col-12 span-box">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[is_package]" value="0" {{ old('product.is_package') == 1 ? 'checked' : null }} />
                    <span class="custom-control-label">محصولات پکیج<span>
                  </label>
                </div>

                <div class="form-group col-12 span-box">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[is_benibox]" value="0" {{ old('product.is_benibox') == 1 ? 'checked' : null }} />
                    <span class="custom-control-label">محصولات بنی باکس<span>
                  </label>
                </div>

                <div class="form-group col-12 span-box">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[is_amazing]" value="0" {{ old('product.is_amazing') == 1 ? 'checked' : null }} />
                    <span class="custom-control-label">محصولات شگفت انگیز<span>
                  </label>
                </div>

                <div class="form-group col-12 span-box">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[free_shipping]" value="0" {{ old('product.free_shipping') == 1 ? 'checked' : null }} />
                    <span class="custom-control-label">ارسال رایگان<span>
                  </label>
                </div>

              </div>
            </div>
          </div>
        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">تنظیمات دیگر</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="col-12 form-group">
                  <label for="title" >اخطار موجودی <span class="text-danger" style="display:inline;">&starf;</span></label>
                  <input type="text" class="form-control" name="product[low_stock_quantity_warning]" placeholder="اخطار موجودی" value="{{ old('product.low_stock_quantity_warning') }}">
                </div>

                <div class="form-group col-12">
                  <label class="custom-control custom-checkbox mb-0">
                    <input type="checkbox" class="custom-control-input" name="product[chargeable]" value="1" {{ old('product.chargeable') == 1 ? 'checked' : null }} checked>
                    <span class="custom-control-label">ارسال نوتیفیکیشن به کاربران در انتظار<span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- row closed -->
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    $(document).ready(function() {
      $('.category-multiple').select2({
        placeholder:'دسته بندی (ها) را انتخاب کنید',
        closeOnSelect:false,
      });

      $('.tag-multiple').select2({
        placeholder:'تگ (ها) را انتخاب کنید',
        closeOnSelect:false,
      });
    });


    //for upload image
    document.getElementById('image-input').addEventListener('change', function (event) {
        const previewContainer = document.getElementById('preview-container');
        previewContainer.innerHTML = '';  // Clear existing images
        Array.from(event.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const imageDiv = document.createElement('div');
                imageDiv.className = 'preview-image';

                const img = document.createElement('img');
                img.src = e.target.result;
                imageDiv.appendChild(img);

                const deleteButton = document.createElement('button');
                deleteButton.className = 'delete-button';
                deleteButton.textContent = 'x';
                deleteButton.addEventListener('click', function () {
                    previewContainer.removeChild(imageDiv);
                });
                imageDiv.appendChild(deleteButton);

                previewContainer.appendChild(imageDiv);
            };
            reader.readAsDataURL(file);
        });

        // Initialize SortableJS
        new Sortable(previewContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost'
        });
    });
    //end upload image

    $(document).ready(function() {
        $('.select2').select2({
            templateResult: formatState
        });
    });

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }

        var colorCode = $(state.element).data('color');
        var $state = $(
            '<span style="display: inline-block; width: 20px; height: 20px; background-color:' + colorCode + '; margin-right: 8px;"></span>' +
            '<span>' + state.text + '</span>'
        );
        return $state;
    }



    var $fromDate = new Date({{request('published_at')}});

    $('#published_at_show').MdPersianDateTimePicker({
        targetDateSelector: '#published_at',
        targetTextSelector: '#published_at_show',
        englishNumber: false,
        fromDate:true,
        enableTimePicker: true,
        dateFormat: 'yyyy-MM-dd',
        textFormat: 'yyyy-MM-dd',
        groupId: 'rangeSelector1',
    });

    window.onload = function() {
        // به بالای صفحه اسکرول می‌کنه
        window.scrollTo(0, 0);
    };


    </script>
@endsection
