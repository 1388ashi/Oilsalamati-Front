<?php

namespace Modules\Customer\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Modules\Customer\Entities\ValidCustomer;

class ValidCustomerController extends Controller
{
    public function index()
    {
        $customers = ValidCustomer::query()
            ->latest('id')
            ->active()
            ->searchkeywords()
            ->get();

        return response()->success('مشتریان معتبر',compact('customers'));
    }

}
