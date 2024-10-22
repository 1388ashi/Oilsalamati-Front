@extends('admin.layouts.master')

@section('content')
<div class="page-header">
  <x-breadcrumb :items="[['title' => 'مرتب سازی محصولات']]"/>
  <div>
    <button id="submitButton" type="submit" class="btn btn-teal align-items-center"><span>ذخیره مرتب سازی</span><i class="fe fe-code mr-1 font-weight-bold"></i></button>
    <x-create-button type="modal" target="edit-sort-product" title="افزودن محصول به لیست" />
  </div>
</div>
    <!-- row opened -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header  border-0">
                    <div class="card-title">مرتب سازی محصولات</div>
                    <div class="card-options">
                        <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                        <a href="#" class="card-options-fullscreen" data-toggle="card-fullscreen"><i class="fe fe-maximize"></i></a>
                        <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
                    </div>
                </div>
                <div class="card-body">
                  @include('components.errors')
                  <form id="myForm" action="{{route('admin.product-order.change-order',)}}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <div id="hr-table-wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <table class="table table-bordered text-nowrap text-center">
                                    <thead>
                                      <tr>
                                        <th class="border-top">انتخاب</th>
                                        <th class="border-top">شناسه</th>
                                        <th class="border-top">محصول</th>
                                        <th class="border-top">عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-center" id="items">
                                      @forelse($products as $product)
                                        <tr>
                                            <input type="hidden" value="{{ $product->id }}" name="ids[]">
                                            <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                            <td>{{$product->id}}</td>
                                            <td>{{$product->title}}</td>
                                            <td>
                                              <button
                                              onclick="confirmDelete('delete-{{ $product->id }}')"
                                              class="btn btn-sm btn-icon btn-danger text-white"
                                              data-toggle="tooltip"
                                              type="button"
                                              data-original-title="حذف"
                                              {{ isset($disabled) ? 'disabled' : null }}>
                                              {{ isset($title) ? $title : null}}
                                              <i class="fa fa-trash-o {{ isset($title) ? 'mr-1' : null }}"></i>
                                            </button>
                                            </td>
                                        </tr>
                                            @empty
                                        <tr>
                                            <td colspan="8">
                                                <p class="text-danger"><strong>در حال حاضر هیچ محصولی یافت نشد!</strong></p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-teal mt-5" type="submit">مرتب سازی محصولات</button>
                  </form>
                </div>
                <!-- table-wrapper -->
            </div>
            <!-- section-wrapper -->
        </div>
    </div>
    @foreach ($products as $product)
      <form
        action="{{ route('admin.order-product.make-order-id-null', $product->id) }}"
        method="POST"
        id="delete-{{ $product->id }}"
        style="display: none">
        @csrf
      </form>
    @endforeach
    <div class="modal fade mt-5"id="edit-sort-product">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('admin.order-product.store')}}" method="post"  class="save" enctype="multipart/form-data">
                    @csrf
                  <div class="modal-header">
                    <p class="modal-title font-weight-bolder">افزودن محصول</p>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                    <div class="modal-body">
                      <div class="col-12">
                        <div class="form-group">
                            <label class="control-label">انتخاب محصول:<span class="text-danger">&starf;</span></label>
                            <select class="form-control select2" name="product_id">
                                <option value="" selected>انتخاب</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : null }}>{{ $product->title }}</option>
                                @endforeach
                            </select>
                        </div>
                      </div>
                     <div class="modal-footer justify-content-center">
                        <button type="submit" class="btn btn-primary text-right item-right">ثبت</button>
                        <button type="button" class="btn btn-outline-danger text-right item-right" data-dismiss="modal">برگشت</button>
                     </div>
                  </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
    document.getElementById('submitButton').addEventListener('click', function() {
        document.getElementById('myForm').submit();
    });
  var items = document.getElementById('items');
  var sortable = Sortable.create(items, {
    handle: '.glyphicon-move',
    animation: 150
  });
  var items = document.getElementById('items');
  var sortable = Sortable.create(items, {
    handle: '.glyphicon-move',
    animation: 150
  });
  </script>
@endsection
