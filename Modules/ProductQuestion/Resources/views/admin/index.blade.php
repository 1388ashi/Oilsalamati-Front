@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'پرسش محصولات']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">پرسش محصولات ({{ $questionsCount }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>نظر</th>
                        <th>محصول</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>پاسخ</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($questions as $question)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td style="white-space: wrap">{{ $question->body }}</td>
                            <td style="white-space: wrap">{{ $question->product->title }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot
                                        name="type">{{ config('productquestion.status_color.' . $question->status) }}</x-slot>
                                    <x-slot
                                        name="text">{{ config('productquestion.statuses.' . $question->status) }}</x-slot>
                                </x-badge>
                            </td>
                            <td>{{ verta($question->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-icon btn-purple text-white position-relative"
                                    @php($isDisabled = $question->parent_id || $question->children->isNotEmpty() ? true : false)
                                    @if (!$isDisabled) data-target="#showQuestionAnswerModal-{{ $question->id }}" 
                data-toggle="modal"
              @else
                disabled="disabled" @endif
                                    style="padding: 1px 6px;">
                                    <i class="fe fe-message-circle"></i>
                                    @if ($question->children->isNotEmpty())
                                        <span
                                            class="font-weight-bold text-white fs-10 d-flex align-items-center justify-content-center position-absolute "
                                            style="
                    content:\2713;
                    background-color: #00d92a;
                    top: -11px;
                    right: -8px;
                    width: 18px;
                    height: 18px;
                    border-radius: 50px;">
                                            &#10003;
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td>
                                @include('core::includes.show-icon-button', [
                                    'model' => $question->id,
                                    'route' => 'admin.product-questions.show',
                                ])

                                @include('core::includes.edit-modal-button', [
                                    'target' => '#editQuestionModal-' . $question->id,
                                ])

                                @include('core::includes.delete-icon-button', [
                                    'model' => $question,
                                    'route' => 'admin.product-questions.destroy',
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">
                    {{ $questions->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @if ($questions->isNotEmpty())
        @include('productquestion::admin.includes.edit-modal')
        @include('productquestion::admin.includes.answer-modal')
    @endif
@endsection
@section('scripts')
    <script>
        function assignStatus(status, questionId) {
            $('#status-' + questionId).attr('value', status);
            $('#assign-status-form-' + questionId).submit();
        }
    </script>
@endsection
