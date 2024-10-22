<?php

namespace Modules\Notification\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Services\NotificationService;
use Modules\Customer\Entities\Customer;
//use Shetabit\Shopit\Modules\Notification\Http\Controllers\Customer\NotificationController as BaseNotificationController;

class NotificationController extends BaseController
{
    public function index(Request $request)
    {
        // افزودن نوتیفیکیشن های عمومی خوانده نشده به لیست نوتیفیکیشن ها
        $user = Auth::user();
        $last_notification_get = $user->last_notification_get??date("Y-m-d H:i:s");
        $notifications_public = DB::table('notifications_public')->where('created_at', '>', $last_notification_get)->get();

        $data = array();
        foreach ($notifications_public as $item) {
            $data[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'public',
                'notifiable_type' => 'Modules\Customer\Entities\Customer',
                'notifiable_id' => Auth::user()->id,
                'data' => json_encode(['description' => $item->data]),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $user->last_notification_get = date("Y-m-d H:i:s");
        $user->save();

        DB::table('notifications')->insert($data);
        // اتمام افزودن لیست نوتیفیکیشن های عمومی


//        dd($request->input('last_created_at'));

//        $request->validate([
//            'last_created_at' => 'nullable|date_format:Y-m-d H:i:s|before:' . now()
//        ]);

        /** @var Customer $customer */
        $customer = Auth::user();

        $notificationsList = DB::table('notifications')
            ->where('notifiable_id',$customer->id)
            ->select('id','read_at','is_notified','type','data','created_at')
            ->orderBy('created_at','desc')
            ->get();

//        $notificationService = new NotificationService($customer);
//        $lastCreatedAt = null;
//        if ($request->filled('last_created_at')) {
//            $lastCreatedAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('last_created_at'));
//        }
        $unread = 0;
        foreach ($notificationsList as $item) {
            $item->data = json_decode($item->data);
            if (!$item->read_at){
                $unread++;
            }
        }
        $notifications['items'] = $notificationsList;
        $notifications['total_unread'] = $unread;

        // علامت زدن نمایش داده شده ها
        foreach ($notificationsList as $item) {
            if (!$item->is_notified){
                DB::table('notifications')->where('id',$item->id)->update(['is_notified' => 1]);
            }
        }

        return response()->success('', compact('notifications'));
    }




    // came from vendor ================================================================================================
    public function read()
    {
        /** @var Customer $customer */
        $customer = \Auth::user();
        $notificationService = new \Shetabit\Shopit\Modules\Core\Services\NotificationService($customer);
        $notificationService->read();

        return response()->success('');
    }

}
