@extends('admin.layouts.master')

@section('styles')
    <style>
        #customInput {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست محصولات پیشنهادی']])
        <x-breadcrumb :items="$items" />
        <div>
            <button id="submitButton" type="submit" class="btn btn-teal submit-disable" style="display: none">
                ذخیره مرتب سازی
                <i class="fe fe-code mr-1"></i>
            </button>
            <button class="btn btn-indigo create-disable" data-target="#createRecommendationModal" data-toggle="modal">ثبت گروه
                محصول
                <i class="fa fa-plus mr-1"></i>
            </button>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست گروه محصولات ({{ number_format($recommendations->count()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.recommendations.sort') }}" id="myForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="table-responsive">
                    <div id="hr-table-wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <table class="table table-striped table-bordered text-nowrap text-center">
                                <thead>
                                    <tr>
                                        <th class="border-top">انتخاب</th>
                                        <th class="border-top">نام گروه</th>
                                        <th class="border-top">عنوان</th>
                                        <th class="border-top">لینک</th>
                                        <th class="border-top">لینک ارجاع</th>
                                        <th class="border-top">وضعیت</th>
                                        <th class="border-top">لیست آیتم های گروه</th>
                                        <th class="border-top">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center" id="items">
                                    @forelse($recommendations as $recommendation)
                                        <tr>
                                            <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                            <input type="hidden" value="{{ $recommendation->id }}"
                                                name="recommendations[]">
                                            <td>{{ $recommendation->group_name }}</td>
                                            <td>{{ $recommendation->title }}</td>
                                            <td>{{ $recommendation->link ? $recommendation->link : '-' }}</td>
                                            @php($i = null)
                                            @if ($recommendation->linkable_id)
                                                @php($i = '|')
                                            @else
                                                @php($recommendation->linkable_id = null)
                                            @endif
                                            <td>
                                                {{ $recommendation->linkable_type ? __('custom.' . $recommendation->linkable_type) . $i . $recommendation->linkable_id : '-' }}
                                            </td>
                                            <td>@include('core::includes.status', [
                                                'status' => $recommendation->status,
                                            ])</td>
                                            <td>@include('core::includes.show-icon-button', [
                                                'model' => $recommendation,
                                                'route' => 'admin.recommendation-items.index',
                                            ])
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.recommendations.edit', $recommendation) }}"
                                                    class="btn btn-sm edit-disable btn-icon btn-warning text-white"
                                                    data-toggle="tooltip" data-original-title="ویرایش">
                                                    {{ isset($title) ? $title : null }}
                                                    <i class="fa fa-pencil {{ isset($title) ? 'mr-1' : null }}"></i>
                                                </a>
                                                <button onclick="confirmDelete('delete-{{ $recommendation->id }}')"
                                                    class="btn btn-sm delete-disable btn-icon btn-danger text-white"
                                                    data-toggle="tooltip" type="button" data-original-title="حذف"
                                                    {{ isset($disabled) ? 'disabled' : '' }}>
                                                    {{ $title ?? '' }}
                                                    <i class="fa fa-trash-o {{ isset($title) ? 'mr-1' : '' }}"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('core::includes.data-not-found-alert', [
                                            'colspan' => 8,
                                        ])
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    @foreach ($recommendations as $recommendation)
        <form action="{{ route('admin.recommendations.destroy', $recommendation->id) }}" method="POST"
            id="delete-{{ $recommendation->id }}" style="display: none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
    @include('product::admin.recommendation.create')
@endsection
@section('scripts')
    <script>
        $('.select2').select2({
            tags: true
        });
        var items = document.getElementById('items');
        var disableButtons = false;

        var sortable = Sortable.create(items, {
            handle: '.glyphicon-move',
            animation: 150,
            onEnd: function(event) {
                disableButtons = true;
                if (disableButtons) {
                    $('.edit-disable').prop('disabled', true);
                    $('.create-disable').prop('disabled', true);
                    $('.delete-disable').prop('disabled', true);
                    $('.submit-disable').show();
                    console.log('Buttons disabled');
                } else {
                    $('.edit-disable').prop('disabled', false);
                    $('.create-disable').prop('disabled', false);
                    $('.delete-disable').prop('disabled', false);
                    console.log('Buttons enabled');
                }
            }
        });


        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });

        var linkables = @json($linkables);

        function toggleInput() {
            let selectOption = document.getElementById('linkableType');
            let linkInput = document.getElementById('link');
            $('#divLinkable').hide();
            $('#divLink').hide();
            linkInput.value = '';

            if (selectOption.value === "self_link") {
                $('#divLink').show();
            } else if (selectOption.value === 'none') {
                $('#divLink').hide();
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
    </script>
@endsection
