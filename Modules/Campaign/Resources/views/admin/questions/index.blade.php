@extends('admin.layouts.master')

@section('content')
<div class="page-header">
  <x-breadcrumb :items="[['title' => 'لیست کمپین ها', 'route_link' => 'admin.campaigns.index'],['title' => 'لیست سوالات']]" />
  <div>
    <button id="submitButton" type="submit" class="btn btn-teal align-items-center"><span>ذخیره مرتب سازی</span><i class="fe fe-code mr-1 font-weight-bold"></i></button>
    <x-create-button type="modal" target="createFaqModal" title="سوال جدید" />
  </div>
</div>

    <!-- row opened -->
<x-card>
  <x-slot name="cardTitle">لیست سوال های کمپین ({{$campaign->title}})</x-slot>
  <x-slot name="cardOptions"><x-card-options /></x-slot>
  <x-slot name="cardBody">
    @include('components.errors')
    <form id="myForm" action="{{route('admin.campaignQuestions.sort',$campaign->id)}}" method="POST">
        @csrf
        @method('PATCH')
        <div class="table-responsive">
          <table class="table table-striped table-bordered text-nowrap text-center">
            <thead>
              <tr>
                <th class="border-top">انتخاب</th>
                <th class="border-top">ردیف</th>
                <th class="border-top">سوال</th>
                <th class="border-top">عملیات</th>
            </tr>
            </thead>
            <tbody id="items" class="text-center" >
              @forelse($questions as $question)
                <tr>
                    <input type="hidden" value="{{ $question->id }}" name="orders[]">
                    <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                    <td class="font-weight-bold">{{$loop->iteration}}</td>
                    <td>{{$question->question}}</td>
                    <td>
                      @include('core::includes.edit-icon-button',[
                        'model' => $question,
                        'route' => 'admin.campaignQuestions.edit',
                      ])
                        <button
                        onclick="confirmDelete('delete-{{ $question->id }}')"
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
                        <p class="text-danger"><strong>در حال حاضر هیچ سوالی یافت نشد!</strong></p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
      </div>
    <button class="btn btn-teal mt-5" type="submit">ذخیره مرتب سازی</button>
    </form>
  </x-slot>
</x-card>
    @foreach ($questions as $question)
    <form
      action="{{ route('admin.campaignQuestions.destroy', $question->id) }}"
      method="POST"
      id="delete-{{ $question->id }}"
      style="display: none">
      @csrf
      @method('DELETE')
    </form>
    @endforeach
    @include('campaign::admin.questions.create')
@endsection
@section('scripts')
    <script>
      const rowContainer = document.getElementById('specification-values-section');
      const selectType = document.getElementById('type');
      const optionsContainer = document.getElementById('options-container');

      selectType.addEventListener('change', function() {
        if (selectType.value === 'options' || selectType.value === 'checkbox') {
            rowContainer.classList.remove('hidden');
        } else {
            rowContainer.classList.add('hidden');
        }
      });
         $(document).ready(() => {
            let counter = 1;
            let specificationValuesGroupRow = $('#specification-values-group-row');
            let specificationValuesSection = $('#specification-values-section');
            let firstNegativeButton = $('#negative-btn-0');

            specificationValuesGroupRow.on('click', '.positive-btn', (event) => {

              let newPositiveBtn = $(event.currentTarget)
                .clone()
                .attr('id', `positive-btn-${counter}`)
                .text('+');

              let newNegativeBtn = firstNegativeButton
                .clone()
                .attr('id', `negative-btn-${counter}`)
                .removeAttr('disabled')
                .text('-');

              let newInput = $(
                `<input
                  id="value-${counter}"
                  name="data[${counter}]"
                  name="data"
                  type="text"
                  placeholder="مقدار"
                  class="form-control mx-1"
                />`
              );

              let newGroup = $('<div class="col-12 d-flex plus-negative-container mt-2"></div>');

              newGroup
                .append(newPositiveBtn)
                .append(newNegativeBtn)
                .append(newInput);

              specificationValuesGroupRow.append(newGroup);

              counter++;

            });

            specificationValuesGroupRow.on('click', '.negative-btn', (event) => {
              $(event.currentTarget).closest('.plus-negative-container').remove();
              counter--;
            });
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
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
    </script>
@endsection
