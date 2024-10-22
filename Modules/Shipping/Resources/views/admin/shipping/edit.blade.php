@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست حمل و نقل ها', 'route_link' => 'admin.shippings.index'],
                ['title' => 'ویرایش حمل و نقل'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>
    <x-card>
        <x-slot name="cardTitle">ویرایش حمل و نقل - کد {{ $shipping->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.shippings.update', $shipping) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="name" class="control-label"> نام: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="name" class="form-control" name="name"
                                value="{{ old('name', $shipping->name) }}" required autofocus />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="default_price" class="control-label"> مبلغ پیش فرض (تومان):</label>
                            <input type="text" id="default_price" class="form-control comma" name="default_price"
                                value="{{ old('default_price', number_format($shipping->default_price)) }}" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="free_threshold" class="control-label"> حد ارسال رایگان (تومان): </label>
                            <input type="text" id="free_threshold" class="form-control comma" name="free_threshold"
                                value="{{ old('free_threshold', number_format($shipping->free_threshold)) }}" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="packet_size" class="control-label"> سایز هر بسته : <span
                                    class="text-danger">&starf;</span></label>
                            <input type="number" id="packet_size" class="form-control" name="packet_size"
                                value="{{ old('packet_size', $shipping->packet_size) }}" required min="1" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="first_packet_size" class="control-label"> سایز اولین بسته : <span
                                    class="text-danger">&starf;</span></label>
                            <input type="number" id="first_packet_size" class="form-control" name="first_packet_size"
                                value="{{ old('first_packet_size', $shipping->first_packet_size) }}" required />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="more_packet_price" class="control-label"> هزینه اضافه به ازای هر بسته (تومان) :
                                <span class="text-danger">&starf;</span></label>
                            <input type="text" id="more_packet_price" class="form-control comma" name="more_packet_price"
                                value="{{ old('more_packet_price', number_format($shipping->more_packet_price)) }}"
                                required />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label for="logo" class="control-label"> لوگو: </label>
                            <input type="file" id="logo" class="form-control" name="logo"
                                value="{{ old('logo') }}">
                        </div>
                    </div>
                    {{-- <div class="col-12 col-md-6 col-lg-3">
            <div class="form-group">
              <label for="provinces" class="control-label"> انتخاب استان ها: </label>
              <select class="form-control select2" multiple id="provinces">
                @foreach ($provinces as $province)
                  <option
                    value="{{ $province->id }}"
                    {{ $shipping->provinces->contains($province->id) ? 'selected' : '' }}>
                    {{ $province->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div> --}}
                    @if ($logo = $shipping->getFirstMedia('logo'))
                        <div class="col-12 text-center">
                            <div class="img-holder my-4 img-show w-100 bg-light" style="max-height: 300px;">
                                <img src="{{ $logo->getUrl() }}" style="max-height: 300px" alt="logo">
                            </div>
                        </div>
                    @endif
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="control-label">توضیحات :</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $shipping->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-12 ">
                        <div class="form-group d-flex">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', $shipping->status) ? 'checked' : '' }} />
                                <span class="custom-control-label">فعال</span>
                            </label>
                            <label class="custom-control custom-checkbox mr-5">
                                <input type="checkbox" class="custom-control-input" name="is_public" value="1"
                                    {{ old('is_public', $shipping->isPublic()) ? 'checked' : '' }} />
                                <span class="custom-control-label">عمومی</span>
                            </label>
                        </div>
                    </div>
                    {{-- <div id="provinces-price-section" class="col-md-8 mx-auto table-responsive mt-4">
            <table id="provinces-price-table" role="table"
                class="table b-table table-hover table-bordered text-center border-top">
                <thead role="rowgroup">
                    <tr role="row">
                        <th role="columnheader" scope="col">استان</th>
                        <th role="columnheader" scope="col">هزینه ارسال (تومان)</th>
                    </tr>
                </thead>
                <tbody role="rowgroup">
                  @if ($shipping->provinces)
                    @foreach ($shipping->provinces as $province)
                      <tr role="row">  
                        <td role="cell" aria-colindex="1" class="province-id">{{ $province->name }}</td>  
                        <td role="cell" aria-colindex="4" class="province-discount">  
                          <input type="hidden" name="provinces[{{ $province->id }}}][id]" value="{{ $province->id }}}">  
                          <input 
                            type="number" 
                            class="form-control" 
                            name="provinces[{{ $province->id }}}][price]" 
                            value="{{ $province->pivot->price }}"   
                            oninput="updateProvincePrice({{$province->id}}, this.value)"
                          />  
                        </td>  
                      </tr>
                    @endforeach
                  @endif
                </tbody>
            </table>
          </div> --}}
                </div>
                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">بروزرسانی</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
{{-- @section('scripts')
    <script>
        let provincePrices = {};

        $(document).ready(() => {

          if (@json($shipping->provinces) == undefined || @json($shipping->provinces).length == 0) {
            $('#provinces-price-section').hide();
          }

            $('#provinces').on('change', function() {
                const selectedProvinces = $(this).val();
                if (!selectedProvinces || selectedProvinces.length === 0) {
                    $('#provinces-price-section').hide();
                    $('#provinces-price-table tbody').empty();
                    return;
                }

                $('#provinces-price-section').show();
                $('#provinces-price-table tbody').empty();

                selectedProvinces.forEach((provinceId) => {
                    const province = {!! json_encode($provinces) !!}.find(c => c.id == provinceId);
                    if (province) {
                        const existingPrice = provincePrices[province.id] || '';
                        $('#provinces-price-table tbody').append(`  
                    <tr role="row">  
                        <td role="cell" aria-colindex="1" class="province-id">${province.name}</td>  
                        <td role="cell" aria-colindex="4" class="province-discount">  
                            <input type="hidden" name="provinces[${province.id}][id]" value="${province.id}">  
                            <input type="number" class="form-control" name="provinces[${province.id}][price]" value="${existingPrice}"   
                                   oninput="updateProvincePrice(${province.id}, this.value)">  
                        </td>  
                    </tr>  
                `);
                    }
                });
            });
        });

        function updateProvincePrice(provinceId, price) {
            provincePrices[provinceId] = price;
        }

        $('#provinces').select2({
            placeholder: 'انتخاب محصول',
        });
    </script>
@endsection --}}
