@extends('admin.layouts.master')
@section('content')
<div class="page-header">
    <x-breadcrumb :items="[['title' => 'لیست سوال ها', 'route_link' => 'admin.campaignQuestions.index','parameter' => $question->campaign_id], ['title' => 'ویرایش سوال']]" />
</div>

<x-card>
    <x-slot name="cardTitle">ویرایش سوال</x-slot>
    <x-slot name="cardOptions"><x-card-options /></x-slot>
    <x-slot name="cardBody">
        @include('components.errors')
        <form action="{{ route('admin.campaignQuestions.update', $question) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="campaign_id" value="{{ $question->campaign_id }}">
                    <div class="form-group">
                        <label class="control-label">سوال :<span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control" name="question" placeholder="سوال را اینجا وارد کنید"
                            value="{{ old('question', $question->question) }}" required autofocus>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="questionType" class="control-label">نوع سوال :<span
                                class="text-danger">&starf;</span></label>
                        <select class="form-control" name="type" id="typeattr" required>
                            <option selected disabled>- انتخاب کنید -</option>
                            <option value="options" @if ($question->type == 'options') selected @endif>یک مقداری</option>
                            <option value="checkbox" @if ($question->type == 'checkbox') selected @endif>چند مقداری
                            </option>
                            <option value="text" @if ($question->type == 'text') selected @endif>تشریحی</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="control-label">جایگاه :<span class="text-danger">&starf;</span></label>
                        <input type="text" class="form-control" name="order"
                            placeholder="جایگاه را اینجا وارد کنید" value="{{ old('order', $question->order) }}"
                            required autofocus>
                    </div>
                </div>
            </div>
            @php($dataJSON = json_decode($question->data)) {{-- true را اضافه کردم تا به عنوان آرایه بازگرداند --}}
            <div class="row my-2" id="attribute-values-section" style="{{ is_null($dataJSON) ? 'none' : 'flex' }}">
                <div class="col-12">
                    <p class="header pr-2 font-weight-bold fs-22">مقادیر سوال</p>
                </div>
                <div class="col-12" id="attribute-values-group">
                    <div class="row" id="attribute-values-group-row">
                        @if (!is_null($dataJSON))
                            @foreach ($dataJSON as $index => $dataValue)
                                <div class="col-3 d-flex positive-negative-container mt-2">
                                    <button type="button" class="positive-btn btn btn-sm btn-success  ml-1">+</button>
                                    <button type="button" class="negative-btn btn btn-sm btn-danger  ml-1">-</button>
                                    <input name="data[{{ $index }}]" type="text"
                                        value="{{ $dataValue ?? '' }}" {{-- اگر وجود نداشت، یک رشته خالی قرار دهید --}}
                                        class="form-control mx-1" />
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="text-center">
                        <button class="btn btn-warning" type="submit">ویرایش و ذخیره</button>
                        <a class="btn btn-outline-danger"
                            href="{{ route('admin.campaignQuestions.index', $question->campaign) }}">برگشت</a>
                    </div>
                </div>
            </div>
        </form>
    </x-slot>
</x-card>
@endsection
@section('scripts')
    <script>
        $(document).ready(() => {

            let counter = 1000;
            let attributeValuesGroupRow = $('#attribute-values-group-row');
            let attributeValuesSection = $('#attribute-values-section');
            let negativeButton =
                '<button id="negative-btn" type="button" class="negative-btn btn btn-danger btn-sm ml-1">-</button>';
            let positiveButton =
                '<button id="positive-btn" type="button" class="positive-btn btn btn-success btn-sm  ml-1">+</button>';


            let hasValue = {
                text: false,
                checkbox: true,
                options: true,
            };

            $('#typeattr').on('input', () => {
                let type = $('#typeattr').val();
                if (hasValue[type]) {
                    $('#attribute-values-section').css('display', 'flex');
                } else {
                    $('#attribute-values-section').css('display', 'none');
                }
            });

            attributeValuesGroupRow.on('click', '.positive-btn', (event) => {

                let newPositiveBtn = $(positiveButton).clone();
                let newNegativeBtn = $(negativeButton).clone()

                let newInput = $(
                    `<input
            name="data[${counter}]"
            type="text"
            placeholder="مقدار"
            class="form-control mx-1"
          />`
                );

                let newGroup = $('<div class="col-4 d-flex positive-negative-container mt-2"></div>');

                newGroup
                    .append(newPositiveBtn)
                    .append(newNegativeBtn)
                    .append(newInput);

                attributeValuesGroupRow.append(newGroup);

                counter++;

            });

            attributeValuesGroupRow.on('click', '.negative-btn', (event) => {
                $(event.currentTarget).closest('.positive-negative-container').remove();
                counter--;
            });

        });
    </script>
@endsection
