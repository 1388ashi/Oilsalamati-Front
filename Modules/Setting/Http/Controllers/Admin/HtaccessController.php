<?php

namespace Modules\Setting\Http\Controllers\Admin;

//class HtaccessController extends \Shetabit\Shopit\Modules\Setting\Http\Controllers\Admin\HtaccessController

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Http\Controllers\BaseController;

class HtaccessController extends BaseController
{
    public function __construct(CoreSettings $coreSettings)
    {
        $this->token = $coreSettings->get('action_token');
    }

    public function index()
    {
        $frontUrl = config('app.front_url');
        $response = Http::asForm()->post("$frontUrl/action.php", [
            'token' => $this->token,
            'read_htaccess' => 1
        ]);

        return response()->success('', [
            'data' => $response->body(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'data' => 'required|string'
        ]);

        $frontUrl = config('app.front_url');
        $response = Http::asForm()->post("$frontUrl/action.php", [
            'token' => $this->token,
            'update_htaccess' => 1,
            'data' => $request->data
        ]);

        if ($response->body() !== 'ok') {
            return response()->error('عملیات شکست خورد');
        }


        return response()->success('عملیات با موفقیت انجام شد', [
            'data' => $response->body(),
        ]);
    }
}
