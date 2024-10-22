<?php

namespace Modules\Customer\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\ChargeType;

class ChargeTypeController extends Controller
{
    public function index()
    {
        $chargeTypes = ChargeType::select('id','title','value','is_gift')->get();

        return response()->success('انواع شارژ حساب',compact('chargeTypes'));
    }
}
