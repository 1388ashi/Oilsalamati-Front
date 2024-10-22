<?php

namespace Modules\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Entities\Admin;
use Modules\Contact\Entities\Contact;
use Modules\Core\Entities\Permission;
use Modules\Core\Services\NotificationService;
use Modules\Setting\Entities\Setting;
//use Shetabit\Shopit\Modules\Admin\Http\Controllers\Admin\HomeController as BaseHomeController;

class HomeController extends Controller
{
    protected Admin $admin;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->admin = Auth::user();

            return $next($request);
        });
    }

    public function index()
    {
        $groupsArray = Setting::getGroups();
        $role = $this->admin->roles()->first();
        if ($role->name === 'super_admin') {
            $permissions = Permission::all('name')->pluck('name')->toArray();
        } else {
            $permissions = $role->permissions()->select('name')->get()->pluck('name')->toArray();
        }

        $admin = Auth::user();
        $notificationService = new NotificationService($admin);
        $notifications = $notificationService->get();
        $totalUnreadNotifications = $notificationService->getTotalUnread();
        $contacts = Contact::latest()->where('status', 0)->take(5)->get();
        $unReadContacts = Contact::query()->where('status', 0)->count();

        return response()->success('', ['setting_groups' => $groupsArray, 'permissions' => $permissions,
            'admin' => $admin, 'notifications' => $notifications, 'total_unread_notifications' => $totalUnreadNotifications,
            'total_unread_contacts' => $unReadContacts, 'contacts' => $contacts
        ]);
    }
}
