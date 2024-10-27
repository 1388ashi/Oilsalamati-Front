<?php

namespace Modules\Contact\Http\Controllers\All;

use Modules\Setting\Entities\Setting;
use Shetabit\Shopit\Modules\Contact\Http\Controllers\All\ContactController as BaseContactController;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Contact\Entities\Repository;
use Modules\Contact\Http\Requests\ContactRequest;
use Modules\Core\Helpers\Helpers;

class ContactController extends Controller
{
    // private $repository;

    // public function __construct(Repository $repository)
    // {
    //     $this->repository = $repository;
    // }
    public function index()
    {
        $settings = Setting::query()->where('private', false)->where('group','site')->groupBy('id')->get()->toArray();

        return view('contact::front.index',compact('settings'));
    }
    /**
     * Store a newly created resource in storage.
     * @param ContactRequest $request
     * @return JsonResponse
     */
    public function store(ContactRequest $request)
    {
        if ($request->input('_wreixcf14135vq2av54') != 'تهران'
            && $request->input('cn8dsada032') != 'ایران') {
            throw Helpers::makeValidationException('پاسخ امنیتی وارد شده اشتباه است', 'captcha');
        }
        $contact = $this->repository->create($request->all());

        return response()->success('پیام شما با موفقیت ارسال شد', ['contact' => $contact]);
    }

    public function create()
    {
        $captchas = [
            [
                'name' => '_wreixcf14135vq2av54',
                'message' => 'نام پایتخت ایران را به فارسی بنویسید',
            ],
            [
                'name' => 'cn8dsada032',
                'message' => 'نام کشور iran را به فارسی بنویسید'
            ]
        ];

        $randomIndex = rand(0, 1);

        return response()->success('', $captchas[$randomIndex]);
    }

}
