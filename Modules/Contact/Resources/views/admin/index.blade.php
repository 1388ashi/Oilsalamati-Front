@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'تماس با ما']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">تماس با ما ({{ $contacts->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
        @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>موضوع</th>
                        <th>متن</th>
                        <th>نام</th>
                        <th>شماره</th>
                        <th>وضعیت</th>
                        <th>پاسخ</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($contacts as $contact)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td style="white-space: wrap">{{ $contact->subject }}</td>
                            <td style="white-space: wrap">{{ $contact->body }}</td>
                            <td style="white-space: wrap">{{ $contact->name ?? '-' }}</td>
                            <td>{{ $contact->phone_number ?? '-' }}</td>
                            <td>
                                <span class="btn-icon  badge badge-{{ $contact->status ? 'success' : 'danger' }} text-white"
                                    style="font-size: 14px;">
                                    {{ $contact->status ? 'خوانده شده' : 'خوانده نشده' }}
                                </span>
                            </td>
                            <td>
                                @if ($contact->answer)
                                    <div class="position-relative">
                                        <span class="font-weight-bold"
                                            style="color: white;font-size: 13px;content:\2713;position: absolute;top: -11px;right: -8px;width: 18px;height: 18px;background: blue;border-radius: 50px;display: flex;align-items: center;justify-content: center;">&#10003;</span>
                                        <button style="background-color: rgba(0, 128, 0, .799);" data-toggle="modal"
                                            type="button" onclick="showAsnwserModal({{ $contact }})"
                                            class="btn btn-sm text-white">پاسخ</button>
                                    </div>
                                @else
                                    <div>
                                        <button style="background-color: rgba(0, 128, 0, .799);" data-toggle="modal"
                                            type="button" onclick="showAsnwserModal({{ $contact }})"
                                            class="btn btn-sm text-white">پاسخ</button>
                                    </div>
                                @endif
                            </td>
                            <td style="white-space: wrap">{{ verta($contact->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @can('modify_contact')
                                    <button type="button" class="btn btn-primary btn-icon btn-sm" data-toggle="modal"
                                        onclick="showDescriptionModal({{ $contact }})" data-original-title="توضیحات">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                @endcan

                                @can('delete_contact')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $contact,
                                        'route' => 'admin.contacts.destroy',
                                        'disabled' => false,
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $contacts->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @include('contact::admin.show-anwser')
    @include('contact::admin.show')
@endsection
@section('scripts')
    <script>
        function showDescriptionModal(contact) {
            let contactId = contact.id;
            let token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('admin.contacts.read') }}',
                type: 'PATCH',
                data: {
                    contact_id: contactId
                },
                headers: {
                    'X-CSRF-TOKEN': token
                },
            });

            let modal = $('#showDescriptionModal');
            modal.modal('show');

            // پاک کردن محتویات قبلی
            modal.find('#description').empty();

            if (contact.name) {
                modal.find('#description').append(
                    `<div class="mb-1 d-flex"> <p class="font-weight-bold ml-1"> نام :</p>${contact.name}</div>`);
            } else {
                modal.find('#description').append(
                    `<div class="mb-1 d-flex"> <p class="font-weight-bold ml-1"> نام :</p>-</div>`);
            }

            if (contact.phone_number) {
                modal.find('#description').append(
                    `<div class="mb-1 d-flex"><p class="font-weight-bold ml-1">شماره همراه :</p> ${contact.phone_number} </div>`
                );
            } else {
                modal.find('#description').append(
                    `<div class="mb-1 d-flex"><p class="font-weight-bold ml-1">شماره همراه :</p>-</div>`);
            }

            modal.find('#description').append(
                `<div class="mb-1 d-flex"><p class="font-weight-bold ml-1">موضوع :</p>${contact.subject}</div>`);
            modal.find('#description').append(
                `<div class="mb-1 d-flex"><p class="font-weight-bold ml-1">متن:</p>${contact.body} </div>`);
        }

        function showAsnwserModal(contact) {
            let contactId = contact.id;


            let modal = $('#showAsnwserModal');
            modal.modal('show');

            // پاک کردن محتویات قبلی
            modal.find('#answer').empty();

            modal.find('#answer').append(
                `<div class="mb-1 d-flex"><p class="font-weight-bold ml-1">موضوع:</p>${contact.subject}</div>`);
            modal.find('#answer').append(
                `<div class="mb-1 mt-2 d-flex"><p class="font-weight-bold ml-1">متن:</p>${contact.body} </div>`);
            if (contact.answer) {
                modal.find('#answer').append(
                    `<div class="mb-1 mt-2 d-flex"><p class="font-weight-bold ml-1">پاسخ:</p>${contact.answer} </div>`);
            } else {
                modal.find('#answer').append(
                    `<div class="mb-1 mt-2 d-flex"><p class="font-weight-bold ml-1">پاسخ :</p>پاسخی داده نشده !</div>`);
            }
            if (contact.answer) {
                modal.find('#answer').append(
                    `<div class="mb-1 d-flex"><textarea class="form-control mt-3" placeholder="پاسخ مورد نظر را وارد کنید ..."  name="answer" cols="70" rows="3">${contact.answer}</textarea></div>`
                );
            } else {
                modal.find('#answer').append(
                    `<div class="mb-1 d-flex"><textarea class="form-control mt-3" placeholder="پاسخ مورد نظر را وارد کنید ..."  name="answer" cols="70" rows="3"></textarea></div>`
                );
            }
            modal.find('#answer').append(`<input type="hidden" name="id" value="${contact.id}">`);
        }
    </script>
@endsection
