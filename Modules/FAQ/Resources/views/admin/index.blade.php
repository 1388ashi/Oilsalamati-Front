@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => ' لیست سوالات متداول']]" />
            <div>
                <button id="submitButton" type="submit" class="btn btn-teal align-items-center"><span>ذخیره مرتب سازی</span><i
                    class="fe fe-code mr-1 font-weight-bold"></i></button>
                @can('write_faq')
                    <x-create-button type="modal" target="createFaqModal" title="سوال جدید" />
                @endcan
            </div>
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">لیست سوالات متداول</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form id="myForm" action="{{ route('admin.faqs.sort') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap text-center">
                        <thead>
                            <tr>
                                <th class="border-top">انتخاب</th>
                                <th class="border-top">ردیف</th>
                                <th class="border-top">سوال</th>
                                <th class="border-top">پاسخ</th>
                                <th class="border-top">وضعیت</th>
                                <th class="border-top">تاریخ ثبت</th>
                                <th class="border-top">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" id="items">
                            @forelse($faqs as $faq)
                                <tr>
                                    <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                    <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                    <input type="hidden" value="{{ $faq->id }}" name="faqs[]">
                                    <td>{{ Str::limit($faq->question, 40, '...') }}</td>
                                    <td>{{ Str::limit($faq->answer, 40, '...') }}</td>
                                    <td>@include('core::includes.status', ['status' => $faq->status])</td>
                                    <td>{{ verta($faq->created_at)->format('Y/m/d H:i') }}</td>
                                    <td>
                                        {{-- Edit --}}
                                        @include('core::includes.edit-modal-button', [
                                            'target' => '#edit-faq-' . $faq->id,
                                        ])
                                        <button onclick="confirmDelete('delete-{{ $faq->id }}')"
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
    @foreach ($faqs as $faq)
        <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" id="delete-{{ $faq->id }}"
            style="display: none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
    @include('faq::admin.edit')
    @include('faq::admin.create')
    <!-- row closed -->
@endsection
@section('scripts')
    <script>
        var items = document.getElementById('items');
        var sortable = Sortable.create(items, {
            handle: '.glyphicon-move',
            animation: 150
        });
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
    </script>
@endsection
