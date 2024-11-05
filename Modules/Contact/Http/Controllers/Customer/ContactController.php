<?php

namespace Modules\Contact\Http\Controllers\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Contact\Entities\Contact;
use Modules\Contact\Http\Requests\Customer\ContactRequest;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        dd('h');
        $contact = Contact::create([
            'subject' =>$request->subject,
            'body' =>$request->body,
            'customer_id' => auth()->user()->id,
        ]);

        return response()->success('پیام شما با موفقیت ارسال شد', ['contact' => $contact]);
    }

    public function index()
    {
        $customer = Auth()->user();
        $contacts = $customer->contacts()->paginate(5);

        return response()->success('لیست مکالمات ارتباط با ما باموفقیت دریافت شد',compact('contacts'));
    }

    public function show(Contact $contact)
    {
        if ($contact->customer_id != auth()->user()->id){
            return response()->error('شما به این پاسخ دسترسی ندارید!');
        }
        return response()->success('',$contact->toArray());
    }
}
