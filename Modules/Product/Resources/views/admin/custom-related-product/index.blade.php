@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item active">لیست محصولات</li>
        </ol>
        <div>
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modelId">
                افزودن محصول
            </button>

{{--            <a href="{{ route('admin.products.create') }}" class="btn btn-indigo">افزودن محصول</a>--}}
        </div>
    </div>

    <!-- row opened -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-0">
                    <p class="card-title" style="font-weight: bold">لیست همه محصولات ({{ $customRelatedProducts->count() }})</p>
                    <div class="card-options">
                        <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i
                                class="fe fe-chevron-up"></i></a>
                        <a href="#" class="card-options-fullscreen" data-toggle="card-fullscreen"><i
                                class="fe fe-maximize"></i></a>
                        <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    @include('components.errors')
                    <div class="table-responsive">
                        <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <table class="table  table-bordered text-nowrap text-center">
                                    <thead>
                                    <tr>
                                        <th class="border-top">شناسه</th>
                                        <th class="border-top">عنوان</th>
                                        <th class="border-top">حذف</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-center">
                                    @forelse($customRelatedProducts as $i => $product)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $product->getProductTitleAttribute() }}</td>
                                                <td>
                                                    <form action="{{ route('admin.custom-related-product.delete',$product->id) }}" method="post">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button class="btn btn-danger btn-sm btn-icon text-white"  type="submit" data-toggle="tooltip" data-original-title="حذف">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <p class="text-danger"><strong>در حال حاضر هیچ محصولی یافت نشد!</strong>
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
{{--                            {{$customRelatedProducts->links()}}--}}
                        </div>
                    </div>
                </div>
                <!-- table-wrapper -->
            </div>
            <!-- section-wrapper -->
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن محصول</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.custom-related-product.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                    <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">انتخاب محصول :</label>
                                <select class="form-control select2" id="filter-products" name="related_id">
                                    <option value="" selected>انتخاب</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : null }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        <input type="hidden" name="product_id" value="{{ request()->id }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    <button type="submit" class="btn btn-primary">افزودن</button>
                </div>
                </form>
            </div>

        </div>
    </div>
@endsection
