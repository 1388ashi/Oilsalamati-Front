<?php

namespace Modules\Campaign\Http\Controllers\Front;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CampaignQuestionController extends Controller
{

    public function index()
    {
        return view('campaign::index');
    }

    #TODO : STORE returned ANSWERS

}
