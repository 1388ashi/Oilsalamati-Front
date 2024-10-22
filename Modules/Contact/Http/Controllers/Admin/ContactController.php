<?php

namespace Modules\Contact\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Contact\Entities\Contact;
use Modules\Contact\Entities\Repository;
use Modules\Contact\Http\Requests\AnswerContactRequest;
//use Shetabit\Shopit\Modules\Contact\Http\Controllers\Admin\ContactController as BaseContactController;
use Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;
class ContactController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      if (request()->header('Accept') == 'application/json') {
        return response()->success('', ['contacts' => Contact::latest('id')->paginate(10)]);
      }
      return view('contact::admin.index',['contacts' => $this->repository->paginate()]);
    }
    public function answer(Request $request)
    {
      $contact = Contact::query()->find($request->id);
      $contact->answer = $request->answer;
      $contact->save();
      ActivityLogHelper::updatedModel('پیام بروز شد', $contact);

      $customerPhone = $contact->customer->mobile;

      return redirect()->route('admin.contacts.index')
      ->with('success', 'پیام با موفقیت حذف شد.');

      // if (!app(CoreSettings::class)->get('sms.patterns.contact_answer', false)) {
      //     return response()->error('الگوی پیامکی شما موجود نیست');
      // }

      // $pattern = app(CoreSettings::class)->get('sms.patterns.contact_answer');
      // Sms::pattern($pattern)->data([
      //     '1' => 'مشتری',
      // ])->to([$customerPhone])->send();

      //   return response()->success('جواب شما ثبت شد', ['contact' => $contact]);
    }


    // came from vendor ================================================================================================

    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the specified resource.
     * @param Contact $contact
     */
    public function show(Contact $contact)
    {
        return response()->success('', ['contact' => $contact]);
    }

    public function read(Request $request)
    {
      $contact = Contact::query()->find($request->contact_id);
      $contact->status = 1;
      $contact->save();

      return response()->success('وضعیت به خوانده شده تغییر کرد', ['contact' => $contact]);
    }

    /**
     * Remove the specified resource from storage.
     * @param Contact $contact
     */
    public function destroy(Contact $contact)
    {
        $this->repository->delete($contact);
        ActivityLogHelper::deletedModel('پیام حذف شد', $contact);


        if (request()->header('Accept') == 'application/json') {
          return response()->success('پیام با موفقیت حذف شد');
        }
        return redirect()->route('admin.contacts.index')
        ->with('success', 'پیام با موفقیت حذف شد.');
    }

}
