@extends('admin.layouts.master')
@section('content')
    <div class="page-header">

        <ol class="breadcrumb align-items-center">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fe fe-home ml-1"></i> داشبورد</a>
            </li>
            <li class="breadcrumb-item"><a href="{{ route('admin.post-categories.index') }}">لیست دسته بندی های مطالب</a></li>
            <li class="breadcrumb-item active">مرتب سازی دسته بندی ها</li>
        </ol>

    </div>
    <div class="card">
        <div class="card-header border-0">
            <p class="card-title">مرتب سازی</p>
            <x-card-options />
        </div>
        <div class="card-body">
            @include('components.errors')
            <form action="{{ route('admin.post-categories.sort') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="table-responsive">
                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <table class="table table-vcenter text-nowrap table-bordered border-bottom">
                                <thead>
                                    <tr>
                                        <th class="text-center">انتخاب</th>
                                        <th class="text-center">الویت</th>
                                        <th class="text-center">عنوان</th>
                                        <th class="text-center">شناسه</th>
                                        <th class="text-center">وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody id="items">
                                    @foreach ($postCategories as $postCategory)
                                        <input type="hidden" value="{{ $postCategory->id }}" name="categories[]">

                                        <tr>
                                            <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                            <td class="text-center">{{ $postCategory->order }}</td>
                                            <td class="text-center">{{ $postCategory->name }}</td>
                                            <td class="text-center">{{ $postCategory->id }}</td>
                                            <td class="text-center">@include('core::includes.status', [
                                                'status' => $postCategory->status,
                                            ])</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <button class="btn btn-teal mt-5" type="submit">ذخیره مرتب سازی</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let items = document.getElementById('items');
        let sortable = Sortable.create(items, {
            handle: '.glyphicon-move',
            animation: 150
        });
    </script>
@endsection
