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

@section('header_script')
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
{{--    <script src="https://unpkg.com/vue-multiselect"></script>--}}
{{--    <link rel="stylesheet" href="https://unpkg.com/vue-multiselect/dist/vue-multiselect.min.css">--}}

{{--    <style src="vue-multiselect/dist/vue-multiselect.min.css"></style>--}}

{{--    <script src="https://unpkg.com/vue-multiselect"></script>--}}
{{--    <link rel="stylesheet" href="https://unpkg.com/vue-multiselect/dist/vue-multiselect.min.css">--}}
@endsection
@section('content')
  <!--  Page-header opened -->
  <div class="page-header">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-life-buoy ml-1"></i> داشبورد</a></li>
      <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.products.index') }}">لیست محصولات</a></li>
      <li class="breadcrumb-item active" aria-current="page">ویرایش محصول</li>
    </ol>
    {{--        <div class="mt-3 mt-lg-0">--}}
    {{--        </div>--}}
  </div>
  <!--  Page-header closed -->


  @include('components.errors')
  <form action="{{ route('admin.products.update',$product->id) }}" method="post" class="save"
        id="companyForm" enctype="multipart/form-data">
      @csrf
      @method('PUT')
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
                                  <input type="text" class="form-control" name="title" placeholder="لطفا نام محصول را وارد کنید" value="{{ old('title',$product->title) }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="title" >موجودی</label>
                                  <input type="text" class="form-control" name="quantity" placeholder="لطفا موجودی محصول را وارد کنید" value="{{ old('quantity',$product->varieties[0]->store->balance) }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="title" >بارکد</label>
                                  <input type="text" class="form-control" name="barcode" placeholder="لطفا بارکد محصول را وارد کنید" value="{{ old('barcode',$product->barcode) }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="sku" >SKU</label>
                                  <input type="text" class="form-control" name="barcode" placeholder="لطفا sku محصول را وارد کنید" value="{{ old('sku',$product->sku) }}">
                                </div>
                                <!--دسته بندی-->
                                <div class="col-12 form-group">
                                  <label class="form-label" style="display:inline;">دسته بندی</label>
                                  <span class="text-danger" style="display:inline;">&starf;</span>
                                  <select class="form-control custom-select select2 category-multiple" multiple="multiple" name="category_id" data-placeholder="دسته بندی را انتخاب کنید ...">
                                    <option value="">دسته بندی را انتخاب کنید</option>
                                    @foreach($categories as $category)
                                      <option value="{{ $category->id }}"{{ ($product->category?->title == $category->title) ? ' selected' : '' }}>
                                        {{ $category->title }}</option>

                                      <!-- show childs -->
                                      @if($category->children)
                                        @foreach($category->children as $child)
                                          <option value="{{ $child->id }}"{{ ($product->category?->title == $child->title) ? ' selected' : '' }}>
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
                                  <select class="form-select form-control" aria-label="واحد" >
                                    {{--                                  <option selected>لطفا واحد را انتخاب کنید</option>--}}
                                    @foreach($units as $unit)
                                      <option value="1">{{$unit->name}}</option>
                                    @endforeach
                                  </select>
                                </div>

                                <div class="col-12 form-group">
                                  <label for="title" >وزن</label>
                                  <input type="text" min="1" class="form-control" name="weight" placeholder="لطفا وزن محصول را وارد کنید" value="{{ old('weight',$product->weight) }}">
                                </div>
                                <div class="col-12 form-group">
                                  <label for="max_limit" >حداکثر تعداد خرید</label>
                                  <input type="number" min="1" class="form-control" name="max_limit" placeholder="لطفا حداکثر تعداد خرید را وارد کنید" value="{{ old('max_limit',$product->max_limit) }}">
                                </div>
                                <!--تگ ها-->
                                <div class="col-12 form-group">
                                  <label class="form-label" style="display:inline;">تگ ها</label>
                                  <span class="text-danger" style="display:inline;">&starf;</span>
                                  <select class="form-control custom-select select2 tag-multiple" multiple="multiple" name="tags[]" data-placeholder="تگ ها را انتخاب کنید ...">
                                    <option value="">تگ را انتخاب کنید</option>
                                    @foreach($tags as $tag)
                                      <option value="{{ $tag->id }}"{{ ($product->tag?->title == $tag->title) ? ' selected' : '' }}>
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
                @include('components.editor',['name' => 'text','required' => 'true','field_name' => 'text','model'=>$product])
            </div>
            </div>
          </div>
        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">تنوع ها</h1>
            </div>
            <div class="card-body">
              <div class="border-0">

                  <div class="col-12 form-group">
                      <label class="form-label" style="display:inline;">تست</label>
                      <span class="text-danger" style="display:inline;">&starf;</span>


                      <div id="app">
                          <p>==============</p>
{{--                          <p v-for="chosenColor in selectedColors">@{{ chosenColor.name }}</p>--}}
{{--                          <p>@{{ selectedColors }}</p>--}}
                          <p>==============</p>
{{--                          <label for="colorSelect">رنگ را انتخاب کنید</label>--}}
{{--                          <select v-model="selectedColors" @change="selectColor" class="form-control custom-select select2 color-multiple" multiple="multiple" name="colors[]" data-placeholder="رنگ‌ها را انتخاب کنید ..." id="colorSelect">--}}
{{--                              <option v-for="color in colors" :value="color.id" :key="color.id">--}}
{{--                                  @{{ color.name }}--}}
{{--                              </option>--}}
{{--                          </select>--}}
{{--                          <div>--}}
{{--                              <strong>رنگ‌های انتخاب شده:</strong>--}}
{{--                              @{{ selectedColors }}--}}
{{--                          </div>--}}

{{--                          <select v-model="selectedColors" class="form-control custom-select select2 color-multiple" multiple="multiple" name="colors[]" data-placeholder="رنگ ها را انتخاب کنید ...">--}}
{{--                              <option value="">رنگ را انتخاب کنید</option>--}}
{{--                              <option v-for="color in colors" :value="color.id" :key="color.id" :data-color="color.code">--}}
{{--                                  @{{ color.name }}--}}
{{--                              </option>--}}
{{--                          </select>--}}


                          <div>
                              <multiselect v-model="value" :options="options"></multiselect>
                          </div>



                          <p>==============</p>


{{--                          <p>@{{ chosenColors }}</p>--}}
                          <p>@{{ num }}</p>
                          <a @click="selectColor">increase</a>
{{--                          <p>@{{ testString }}</p>--}}


                      </div>


                      <script>
                          // const chosenColors = ref([]);
                          var laravelTestString = @json($testString);
                          var laravelColors = @json($colors);
                          import Multiselect from 'Multiselect.vue'
{{--                          alert({{ asset('assets/multiSelectVue/Multiselect.vue') }});--}}
                          // import Multiselect from './Multiselect.vue'
                          // import Multiselect from '/assets/multiSelectVue/Multiselect.vue'


                          const app = Vue.createApp(
                              {
                                  el: '#app',
                                  data() {
                                      return {
                                          colors: laravelColors,
                                          title: "hi title",
                                          num: 12,
                                          selectedColors: [],
                                          testString: laravelTestString,
                                          value: null,
                                          options: ['list', 'of', 'options']
                                      }
                                  },
                                  methods: {
                                      // welcome(){
                                      //     return this.title + " hello welcome method"
                                      // },
                                      selectColor(){
                                          if(this.selectedColors.length >0){
                                              alert(1)
                                          }
                                          this.num ++
                                          // this.chosenColors.push(color);
                                      }
                                  },
                                  watch: {
                                      selectedColors: function (val) {
                                          this.selectColor();
                                      },
                                  },
                              }
                          );

                          app.component('multiselect', Multiselect);


                          app.mount('#app');
                      </script>




                      <select class="form-control custom-select select2 color-multiple" multiple="multiple" name="colors[]" data-placeholder="رنگ ها را انتخاب کنید ...">
                          <option value="">رنگ را انتخاب کنید</option>
                          @foreach($colors as $color)
                              <option value="{{ $color->id }}" data-color="{{ $color->code }}"
                              @foreach($product->varieties as $variety)
                                  @if($variety->color?->name == $color->name)
                                      {{ 'selected' }}
                                      @endif
                                  @endforeach
                              >
                                  {{ $color->name }}
                              </option>
                          @endforeach
                      </select>
                  </div>



                  <div class="col-12 form-group">
                      <label class="form-label" style="display:inline;">رنگ ها</label>
                      <span class="text-danger" style="display:inline;">&starf;</span>
                      <select class="form-control custom-select select2 color-multiple" multiple="multiple" name="colors[]" data-placeholder="رنگ ها را انتخاب کنید ...">
                          <option value="">رنگ را انتخاب کنید</option>
                          @foreach($colors as $color)
                              <option value="{{ $color->id }}" data-color="{{ $color->code }}"
                                      @foreach($product->varieties as $variety)
                                          @if($variety->color?->name == $color->name)
                                          {{ 'selected' }}
                                          @endif
                                      @endforeach
                              >
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
                              <option value="{{ $attribute->id }}" data-color="{{ $attribute->code }}"
                                  @foreach($product->varieties as $variety)
                                      @foreach($variety->attributes as $attribute)
                                          @if($product->attribute->name == $attribute->name)
                                              @selected
                                              @endif

                                          @endforeach

                                      @endforeach
                              >
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

                      </select>
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

        </div>
        <div class="bg-white widget-user mb-5">
          <div class="card">
            <div class="card-header">
              <h1 class="card-title">عکس ها</h1>
            </div>
            <div class="card-body">
              <div class="border-0">
                  <div class="upload-container">
                      <input type="file" id="image-input" class="form-control" multiple accept="image/*">
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
                  @foreach($specifications as $i => $specification)
                      <input type="hidden" name="specifications[{{$i}}][id]" value="{{ $specification->id }}" >

                      <tr>
                      <th>{{ $specification->label }}</th>
                      <td>
                          @if($specification->type == 'text')
{{--                            1--}}
                          @php
                            $flag=false;
                          @endphp
                          @foreach($product->specifications as $productSpecification)
                            @if($productSpecification->id == $specification->id)
                              <input type="text" value="{{ $productSpecification->pivot?->value }}" name="specifications[{{$i}}][value]" class="form-control" placeholder="" aria-label="Username" aria-describedby="basic-addon1">
                              @php
                                $flag= true; //flag true yani peyda shode
                              @endphp
{{--                              {{ dump($productSpecification->pivot->specificationValues[0]['value'] ?? '0') }}--}}
                            @endif
                          @endforeach
                            @if($flag==false)
                                  <input type="text" value="{{ $productSpecification->pivot?->value }}" name="specifications[{{$i}}][value]" class="form-control" placeholder="" aria-label="Username" aria-describedby="basic-addon1">
                            @endif

                          @elseif($specification->type == 'select')
{{--2--}}
                          @php
                            $flag=false;
                            $specification_ids=array();
                          @endphp
                            <select class="form-control" id="exampleFormControlSelect1" name="specifications[{{$i}}][value]">
                                @foreach($product->specifications as $productSpecification)
                                @if ($productSpecification->type == 'select')

{{--                                    <input type="hidden" name="specifications[{{$i}}][id]" value="{{ $productSpecification->id }}" >--}}
                                    @if($productSpecification->id == $specification->id)
                                        @foreach ($productSpecification->values as $productSpecificationVal)
                                            <option value="{{$productSpecificationVal->id}}" selected>{{$productSpecificationVal->value}}</option>
                                        @endforeach
                                        {{-- @php
                                            $flag= true; //flag true yani peyda shode
                                            array_push($specification_ids,$productSpecification->pivot->specificationValue->id);
                                        @endphp --}}
                                    @else
                                    @foreach ($specification->values as $specificationVal)

                                    @endforeach
                                        <option value="{{$specificationVal->id}}">{{$specificationVal->value}}</option>
                                    @endif
                                @endif
                                @endforeach
                              {{-- @if($flag==true)
                                @foreach($specification->values as $specificationValue)
                                    @if(in_array($specificationValue->id,$specification_ids) == false)
                                            <option>{{ $specificationValue->value }}</option>
                                    @endif
                                @endforeach
                              @endif --}}
                          </select>
                          @elseif($specification->type == 'multi_select')

                              @php
                                  $flag = false;
                              @endphp
                              <select class="form-control custom-select select2 tag-multiple multiple" multiple="multiple" name="specifications[{{$i}}][value]" data-placeholder="انتخاب مقدار ...">
                                  @foreach($specification->values as $item)
                                      <option value="{{ $item->id }}"
                                      @foreach($product->specifications as $productSpecification)
                                            @if($productSpecification->id == $specification->id)
                                                @foreach($productSpecification->pivot->specificationValues as $value)
                                                  @if($value->id == $item->id)
                                                      {{ 'selected' }}
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                      >
                                          {{ $item->value }}
                                      </option>
{{--                                      <input type="hidden" name="specifications[{{$i}}][id]" value="{{ $productSpecification->id }}" >--}}
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
                      <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                             class="form-control" id="meta_title"
                             placeholder="تیتر متا را اینجا وارد کنید"  autofocus>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-group">
                      <label for="meta_description" class="control-label">توضیحات متا</label>
                      <textarea name="meta_description"  class="form-control" id="meta_description"
                                rows="3" cols="30" placeholder="توضیحات متا را اینجا وارد کنید"
                                autofocus>{{ old('meta_description') }}</textarea>
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
                <button class="btn btn-warning" type="submit">ثبت و بروزرسانی</button>
              </div>

            </div>
            <div class="card-body">
              <div class="border-0">
                <div class="text-right">
                  <div class="form-group col-12">
                    <label for="status">وضعیت</label>
                    <select class="form-control form-select-sm" name="status" aria-label=".form-select-sm example">
                      @foreach(\Modules\Product\Entities\Product::getAvailableStatuses() as $status)
                        <option value="{{$status}}"
                                @if($status == $product->status)
                                  selected
                                @endif
                        >
                          {{\Modules\Product\Entities\Product::getStatusLabelAttribute($status)}}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-12">
                    <span>زمانبندی کردن انتشار</span>
                    <input type="checkbox" class="" name="status"
                      {{ $product->published_at != null ? ' checked' : '' }}
                    >
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
                                         value="{{ old('published_at',$product->published_at) }}"
                                         placeholder="تاریخ انتشار..." type="text">
                                  <input hidden name="published_at" id="published_at" type="text">
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
                  <input type="text" name="unit_price" value="{{ old('unit_price',number_format($product->unit_price)) }}"
                         class="form-control comma" id="amount" min="0"
                         placeholder="قیمت واحد را اینجا وارد کنید" required autofocus>
                </div>
                <div class="form-group">
                  <label for="discount" class="control-label">تخفیف</label>
                  <input type="text" name="discount" value="{{ old('discount',$product->discount) }}"
                         class="form-control comma" id="discount" min="0"
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
                  <span>قابل شارژ</span>
                  <input type="checkbox" class="" name="status"
                    {{ $product->chargable != null ? ' checked' : '' }}
                  >
                </div>

                <div class="form-group col-12 span-box">
                  <span>نمایش در لیست محصولات جدید</span>
                  <input type="checkbox" class="" name="new_product_in_home"
                    {{ $product->new_product_in_home != null ? ' checked' : '' }}
                  >
                </div>

                <div class="form-group col-12 span-box">
                  <span>محصولات پکیج</span>
                  <input type="checkbox" class="" name="is_package"
                    {{ $product->is_package != null ? ' checked' : '' }}
                  >
                </div>

                <div class="form-group col-12 span-box">
                  <span>محصولات بنی باکس</span>
                  <input type="checkbox" class="" name="is_benibox"
                    {{ $product->is_benibox != null ? ' checked' : '' }}
                  >
                </div>

                <div class="form-group col-12 span-box">
                  <span>محصولات شگفت انگیز</span>
                  <input type="checkbox" class="" name="is_amazing"
                    {{ $product->is_amazing != null ? ' checked' : '' }}
                  >
                </div>

                <div class="form-group col-12 span-box">
                  <span>ارسال رایگان</span>
                  <input type="checkbox" class="" name="free_shipping"
                    {{ $product->free_shipping != null ? ' checked' : '' }}
                  >
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
                  <label for="title" >اخطار موجودی</label>
                  <input type="text" class="form-control" name="low_stock_quantity_warning" placeholder="اخطار موجودی" value="{{ old('low_stock_quantity_warning',$product->low_stock_quantity_warning) }}">
                </div>

                <div class="form-group col-12">
                  <span>ارسال نوتیفیکیشن به کاربران در انتظار</span>
                  <input type="checkbox" class="" name="chargeable"
                    {{ $product->chargeable != null ? ' checked' : '' }}
                  >
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
