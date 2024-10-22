@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست منو ها']]" />
            <div>
                <button id="submitButton" type="submit" class="btn btn-teal align-items-center"><span>ذخیره مرتب سازی</span><i
                    class="fe fe-code mr-1 font-weight-bold"></i></button>
                <x-create-button type="modal" target="createMenuModal" title="منو جدید" />
            </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست منو ها</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form id="myForm" action="{{ route('admin.menu.sort', $menu_items[0]->group_id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap text-center">
                        <thead>
                            <tr>
                                <th class="border-top">انتخاب</th>
                                <th class="border-top">عنوان</th>
                                <th class="border-top">تعداد فرزند ها</th>
                                <th class="border-top">صفحه جدید</th>
                                <th class="border-top">وضعیت</th>
                                <th class="border-top">تاریخ ثبت</th>
                                <th class="border-top">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" id="items">
                            @forelse($menu_items as $menu_item)
                                @php($group = $menu_item->group_id)
                                <tr>
                                    <input type="hidden" value="{{ $menu_item->group }}" name="group">
                                    <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                    <input type="hidden" value="{{ $menu_item->id }}" name="orders[]">
                                    @php($count = count($menu_item->children))
                                    <td class="">
                                        @if ($count == 0)
                                            {{ $menu_item->title }}
                                        @else
                                            <a class="text-info" data-original-title="مشاهده"
                                                href="{{ route('admin.menu.childIndex', [$group, $menu_item]) }}">{{ $menu_item->title }}</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($count == 0)
                                            {{ $count }}
                                        @else
                                            <a class="text-info" data-original-title="مشاهده"
                                                href="{{ route('admin.menu.childIndex', [$group, $menu_item]) }}">{{ $count }}</a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($menu_item->new_tab)
                                            <span class=""><i
                                                    class="text-success fs-26 fa fa-check-circle-o"></i></span>
                                        @else
                                            <span class=""><i class="text-danger fs-26 fa fa-close"></i></span>
                                        @endif
                                    </td>
                                    <td>@include('core::includes.status', [
                                        'status' => $menu_item->status,
                                    ])</td>
                                    <td>{{ verta($menu_item->created_at)->format('Y/m/d H:i') }}</td>
                                    <td>
                                        @include('core::includes.edit-modal-button', [
                                            'target' => '#edit-menu-' . $menu_item->id,
                                        ])
                                        <button onclick="confirmDelete('delete-{{ $menu_item->id }}')"
                                            class="btn btn-sm btn-icon btn-danger text-white" data-toggle="tooltip"
                                            type="button" data-original-title="حذف"
                                            {{ isset($disabled) ? 'disabled' : null }}>
                                            {{ isset($title) ? $title : null }}
                                            <i class="fa fa-trash-o {{ isset($title) ? 'mr-1' : null }}"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                @include('core::includes.data-not-found-alert', ['colspan' => 7])
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-teal mt-5" type="submit">ذخیره مرتب سازی</button>
            </form>
        </x-slot>
    </x-card>
    @foreach ($menu_items as $menu_item)
        <form action="{{ route('admin.menu.destroy', $menu_item->id) }}" method="POST" id="delete-{{ $menu_item->id }}"
            style="display: none">

            @csrf
            @method('DELETE')
        </form>
    @endforeach
    {{--   create menu modal --}}
    <x-modal id="createMenuModal" size="md">
        <x-slot name="title">ثبت منو جدید</x-slot>
        <x-slot name="body">
            <form action="{{ route('admin.menu.store') }}" method="post">
                @csrf
                <input type="hidden" value="{{ $group }}" name="group_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">عنوان:<span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control" name="title" placeholder="عنوان منو را اینجا وارد کنید"
                            value="{{ old('title') }}" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="control-label">پدر:</label>
                        <select name="parent_id" class="form-control select2">
                            <option selected disabled>- انتخاب کنید -</option>
                            @foreach ($menu_items as $menu_item)
                                <option value="{{ $menu_item->id }}">{{ $menu_item->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-12 form-group">
                            <label class="control-label">نوع لینک :</label><span class="text-danger">&starf;</span>
                            <select id="linkableType" onchange="toggleInput()" name="linkable_type" class="form-control">
                                <option value="self_link" class="custom-menu">لینک دلخواه</option>
                                @foreach ($linkables as $link)
                                    <option value="{{ $link['unique_type'] }}" class="model">{{ $link['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 form-group" id="divLinkable" style="display: none">
                            <label class="control-label">آیتم های لینک :</label>
                            <select id="linkableId" name="linkable_id" class="form-control select2">
                                <option class="custom-menu">انتخاب</option>
                            </select>
                        </div>
                        <div class="col-12 form-group">
                            <label class="control-label">لینک دلخواه :</label>
                            <input type="text" id="link" name="link" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="row">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="label" class="control-label"> تب جدید: </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="new_tab" value="1"
                                        {{ old('new_tab', 1) == 1 ? '' : null }} />
                                    <span class="custom-control-label">فعال</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-primary  text-right item-right">ثبت</button>
                        <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-modal>

    {{--  edit menu modal  --}}
    @foreach ($menu_items as $editItem)
        <x-modal id="edit-menu-{{ $editItem->id }}" size="md">
            <x-slot name="title">ویرایش منو</x-slot>
            <x-slot name="body">
                <form action="{{ route('admin.menu.update', [$editItem->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" value="{{ $group }}" name="group_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">عنوان:<span class="text-danger">&starf;</span></label>
                            <input type="text" class="form-control" name="title"
                                placeholder="عنوان منو را اینجا وارد کنید" value="{{ old('title', $editItem->title) }}"
                                required autofocus>
                        </div>
                        <div class="form-group">
                            <label class="control-label">پدر:</label>
                            <select name="parent_id" class="form-control select2">
                                <option selected disabled>- انتخاب کنید -</option>
                                @foreach ($menu_items as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-12 form-group">
                                <label class="control-label">نوع لینک :</label><span class="text-danger">&starf;</span>
                                <select name="linkable_type" onchange="toggleEditInput(this.value,{{ $editItem->id }})"
                                    class="form-control" id="typeLink-{{ $editItem->id }}">
                                    @foreach ($linkables as $link)
                                        <option class="model" value="{{ $link['unique_type'] }}"
                                            @if ($link['linkable_type'] == $editItem->linkable_type) selected @endif>{{ $link['label'] }}</option>
                                    @endforeach
                                    <option value="self_link2" @if ($editItem->link) selected @endif
                                        class="custom-menu">لینک دلخواه</option>
                                </select>
                            </div>
                            <div class="col-12 form-group " id="divLinkableEditId-{{ $editItem->id }}"
                                style="display: none">
                                <label class="control-label">آیتم های لینک :</label>
                                <select name="linkable_id" id="linkableEditId-{{ $editItem->id }}"
                                    class="form-control select2">
                                    <option class="custom-menu">انتخاب</option>
                                </select>
                            </div>
                            <div class="col-12 form-group">
                                <label class="control-label">لینک دلخواه :</label>
                                <input id="linkEdit-{{ $editItem->id }}" type="text" name="link"
                                    class="form-control" value="{{ old('link', $editItem->link) }}" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="label" class="control-label"> وضعیت: </label>
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="status"
                                            id="status" value="1"
                                            {{ old('status', $editItem->status) == 1 ? 'checked' : null }} />
                                        <span class="custom-control-label">فعال</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="label" class="control-label"> تب جدید: </label>
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="new_tab"
                                            value="1"
                                            {{ old('new_tab', $editItem->new_tab) == 1 ? 'checked' : null }} />
                                        <span class="custom-control-label">فعال</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="submit" class="btn btn-warning text-right item-right">به روزرسانی</button>
                            <button type="button" class="btn btn-outline-danger text-right item-right"
                                data-dismiss="modal">برگشت</button>
                        </div>
                    </div>
                </form>
            </x-slot>
        </x-modal>
    @endforeach
    <!-- row closed -->
@endsection
@section('scripts')
    <script>
        {{-- var linkables = @json($linkables); --}}
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
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
        function toggleInput() {
            let selectOption = document.getElementById('linkableType');
            let linkInput = document.getElementById('link');
            $('#divLinkable').hide();
            // Reset linkableId
            linkInput.value = '';
            linkInput.disabled = true;

            if (selectOption.value === "self_link") {
                linkInput.disabled = false;
            } else if (selectOption.value === 'none') {
                linkInput.disabled = true;
            } else {
                $('#divLinkable').show();
                let linkables = @json($linkables);
                let linkableId = document.getElementById('linkableId');
                linkableId.innerHTML = '';

                let findedItem = linkables.find(linkable => {
                    return linkable.unique_type === document.getElementById('linkableType').value;
                });

                if (findedItem) {
                    let option = '';
                    if (findedItem.models !== null) {
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

        function toggleEditInput(editItem, id) {
            let selectOption2 = $(`#typeLink-${id}`).val();
            let linkInput = $(`#linkEdit-${id}`);
            $(`#divLinkableEditId-${id}`).hide();
            linkInput.value = '';
            linkInput.disabled = true;
            if (selectOption2 == "self_link2") {
                linkInput.removeAttr('disabled');
            } else {

                linkInput.attr("disabled", "disabled");
                $(`#divLinkableEditId-${id}`).show();
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
