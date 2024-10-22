<?php

namespace Modules\Newsletters\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Newsletters\Http\Controllers\Admin\UsersNewslettersController as BaseUsersNewslettersController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Newsletters\Entities\Newsletters;
use Modules\Newsletters\Entities\UsersNewsletters;
use Modules\Newsletters\Jobs\SendNewslettersJob;

class UsersNewslettersController extends Controller
{
    public function index()
    {
        $userNewsletters = UsersNewsletters::query()->filters()->latest()->paginate();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('', compact('userNewsletters'));
        }
        return view('newsletters::admin.user.index',compact('userNewsletters'));
    }

    public function destroy(UsersNewsletters $usersNewsletters)
    {
        $usersNewsletters->delete();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('کاربر از لیست خبرنامه با موفقیت حذف شد', compact('usersNewsletters'));
        }
        return redirect()->route('admin.newsletters.index')->with([
          'success' => 'کاربر از لیست خبرنامه با موفقیت ارسال شد']);
    }
}
