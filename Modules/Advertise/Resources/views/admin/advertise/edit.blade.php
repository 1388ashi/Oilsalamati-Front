@extends('admin.layouts.master')
@section('content')
<div class="page-header">
    <x-breadcrumb :items="[['title' => 'لیست جایگاه ها', 'route_link' => 'admin.positions.index'], ['title' => 'ویرایش بنر']]" />
    @can('write_page')
    <x-create-button type="modal" target="createAdvertisementsModal" title="بنر جدید" />
    @endcan
</div>
<!--  Page-header closed -->

<!-- row opened -->
<x-card>
    <x-slot name="cardTitle">ویرایش بنرهای {{$position->name}} شماره {{$position->id}}</x-slot>
    <x-slot name="cardOptions"><x-card-options /></x-slot>
    <x-slot name="cardBody">
        @include('components.errors')
        <form action="{{ route('admin.advertisements.update_possibility',$position) }}" method="post">
            @csrf
            @method('PATCH')
            <div class="table-responsive ">
                <table id="example-2" class="table table-striped table-bordered text-nowrap text-center">
                    <thead>
                            <tr>
                                <th class="border-top">ردیف</th>
                                <th class="border-top">تصویر</th>
                                <th class="border-top">در صد احتمال</th>
                                <th class="border-top">عملیات</th>
                            </tr>
                    </thead>
                    <tbody>
                        @foreach ($position->advertisements as $item)
                        <tr>
                            <td class="font-weight-bold">{{$loop->iteration}}</td>
                            <td>
                                @if ($item->getMedia('picture')->first()->getUrl())
                                <div>
                                    <div class="col-12 mt-2">
                                    <figure class="figure mt-1">
                                        <img src="{{ $item->getMedia('picture')->first()->getUrl() }}" class="img-thumbnail" alt="image" width="150" style="max-height: 80px;" />
                                    </figure>
                                    </div>
                                </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <input type="hidden" name="banner_ids[]" value="{{ $item->id }}">
                                <input id="banner-label"  name="banner_possibility[]" type="text" class="form-control" value="{{$item->possibility}}">
                            </td>
                            <td>
                                @include('core::includes.edit-modal-button',[
                                    'target' => "#edit-position-".$item->id
                                ])
                            <button
                            onclick="confirmDelete('delete-{{ $item->id }}')"
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col">
                    <div class="text-center">
                        <button class="btn btn-warning" type="submit">به روزرسانی</button>
                    </div>
                </div>
            </div>
        </form>
    </x-slot>
</x-card>
@foreach ($position->advertisements as $item)
<form
  action="{{ route('admin.advertise.destroy', $item->id) }}"
  method="POST"
  id="delete-{{ $item->id }}"
  style="display: none">
  @csrf
  @method('DELETE')
  <input type="hidden" name="position_id" value="{{$position->id}}">
</form>
@endforeach
@foreach($position->advertisements as $item)
<x-modal id="edit-position-{{ $item->id }}" size="lg">
    <x-slot name="title">ویرایش بنر</x-slot>
    <x-slot name="body">
        <form action="{{route('admin.advertise.update', [$item->id])}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="col-12 form-group">
                    <label class="control-label">لینک:<span class="text-danger">&starf;</span></label>
                    <input type="text" class="form-control" name="link"  placeholder="لینک را اینجا وارد کنید" value="{{ old('link',$item->link) }}" required autofocus>
                </div>
                <div class="col-12 form-group">
                    <label class="control-label">تصویر:</label>
                    <input  class="form-control" type="file" name="image">
                </div>
                <input type="hidden" name="position_id" value="{{$position->id}}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                        <label for="label" class="control-label"> تب جدید: </label>
                        <label class="custom-control custom-checkbox">
                            <input
                            type="checkbox"
                            class="custom-control-input"
                            name="new_tab"
                            value="1"
                            {{ old('new_tab', $item->new_tab) == 1 ? 'checked' : null }}
                            />
                            <span class="custom-control-label">فعال</span>
                        </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="submit" class="btn btn-warning text-right item-right">به روزرسانی</button>
                <button type="button" class="btn btn-outline-danger text-right item-right" data-dismiss="modal">برگشت</button>
            </div>
        </form>
    </x-slot>
</x-modal>
@endforeach
<!-- row closed -->
@include('advertise::admin.advertise.create')
@endsection
@section('scripts')
  @include('core::includes.date-input-script', [
    'dateInputId' => 'from_published_at_hide',
    'textInputId' => 'from_published_at_show'
  ])
  @include('core::includes.date-input-script', [
    'dateInputId' => 'from_end_at_hide',
    'textInputId' => 'from_end_at_show'
  ])
@endsection
