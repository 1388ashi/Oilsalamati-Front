<?php

namespace Modules\Notification\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Services\NotificationService;
use Modules\Notification\Entities\Notification;
use Modules\Notification\Entities\NotificationPublic;
//use Shetabit\Shopit\Modules\Notification\Http\Controllers\Admin\NotificationController as BaseNotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\Admin\Entities\Admin;
use Illuminate\Support\Str;
class NotificationController extends BaseController
{
    public function index_public()
    {
        $notifications_public = NotificationPublic::select('id','data as text','created_at')->latest('id')->paginate(50);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('', compact('notifications_public'));
        }
        return view('notification::admin.index',compact('notifications_public'));
    }

    public function get_public($id)
    {
        $notification_public = NotificationPublic::select('id','data as text','created_at')->findOrFail($id);
        return response()->success('', compact('notification_public'));
    }

    public function add_public(Request $request)
    {
        $request->validate([
            'text' => 'required',
        ]);
        $notification_public = new NotificationPublic();
        $notification_public->data = $request->text;
        $notification_public->save();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('نوتیفیکیشن عمومی جدید ایجاد شد');
        }
        return redirect()->route('admin.notifications_public.index')
        ->with('success', 'نوتیفیکیشن عمومی جدید ایجاد شد');
    }

    public function update_public(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'text' => 'required',
        ]);
        $notification_public = NotificationPublic::where('id',$request->id)->first();
        $notification_public->data = $request->text;
        $notification_public->save();
        return response()->success('متن نوتیفیکیشن به روز شد');
    }
    public function delete_public($id)
    {
        $notification_public = NotificationPublic::find($id);
        $notification_public->delete();

        if (request()->header('Accept') == 'application/json') {
          return response()->success('نوتیفیکیشن موردنظر حذف شد');
        }
        return redirect()->route('admin.notifications_public.index')
        ->with('success', 'نوتیفیکیشن موردنظر حذف شد');
    }



    public function index_public_for_selected()
    {
        $notifications_public_for_selected = DB::table('notifications as n')
//            ->join('customers as c','c.id', '=', 'n.notifiable_id')
            ->select(
//                'n.id',
                'n.public_uuid as id',
                "n.data",
//                DB::raw("REPLACE(JSON_EXTRACT(n.data, '$.description'),'\"','') as text"),
//                DB::raw("CONCAT(c.first_name , ' ', c.last_name) as receiver_full_name"),
                'n.created_at'
            )
            ->where('type','public')
            ->whereNotNull('n.public_uuid')
            ->selectRaw('count(n.public_uuid) as customers_count')
            ->groupBy('n.public_uuid')
            ->paginate(50);
        foreach ($notifications_public_for_selected as $item) {
            $item->data = json_decode($item->data);
        }
        return response()->success('', compact('notifications_public_for_selected'));
    }

    public function get_public_for_selected($id)
    {
        $notification = DB::table('notifications as n')
//            ->join('customers as c','c.id', '=', 'n.notifiable_id')
            ->select(
//                'n.id',
                'n.public_uuid as id',
                "n.data",
//                DB::raw("REPLACE(JSON_EXTRACT(n.data, '$.description'),'\"','') as text"),
//                DB::raw("CONCAT(c.first_name , ' ', c.last_name) as receiver_full_name"),
                'n.created_at'
            )
            ->where('n.public_uuid',$id)
            ->first();

        $customers = DB::table('customers')
            ->whereIn('id',DB::table('notifications')->where('public_uuid',$id)->pluck('notifiable_id')->all())
            ->select('id','first_name','last_name','mobile')
            ->get();
        return response()->success('', compact(['notification','customers']));
    }

    public function add_public_for_selected(Request $request)
    {
        $request->validate([
            'ids' => 'required',
            'text' => 'required',
        ]);

        $ids = explode(',',$request->ids);
        $public_uuid = Str::uuid()->toString();

        $data = array();
        foreach ($ids as $item) {
            $data[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'public',
                'notifiable_type' => 'Modules\Customer\Entities\Customer',
                'notifiable_id' => $item,
                'data' => json_encode(['description' => $request->text]),
                'public_uuid' => $public_uuid,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
        }

        DB::table('notifications')->insert($data);

        return response()->success('نوتیفیکیشن عمومی جدید برای کاربران انتخابی ایجاد شد');
    }

    public function update_public_for_selected(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'ids' => 'required',
            'text' => 'required',
        ]);
        Notification::where('public_uuid',$request->id)->delete();

        $ids = explode(',',$request->ids);
        $public_uuid = Str::uuid()->toString();

        $data = array();
        foreach ($ids as $item) {
            $data[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'public',
                'notifiable_type' => 'Modules\Customer\Entities\Customer',
                'notifiable_id' => $item,
                'data' => json_encode(['description' => $request->text]),
                'public_uuid' => $public_uuid,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
        }

        DB::table('notifications')->insert($data);

        return response()->success('نوتیفیکیشن جدیدی ایجاد و ارسال شد');
    }
    public function delete_public_for_selected(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        Notification::where('public_uuid',$request->id)->delete();
        return response()->success('نوتیفیکیشن خصوصی موردنظر حذف شد');
    }






// came from vendor ================================================================================================
    public function index(Request $request)
    {
      //        dd($request->input('last_created_at'));
      $request->validate([
        'last_created_at' => 'nullable|date_format:Y-m-d H:i:s|before:' . now()
      ]);
        /** @var Admin $admin */
        $admin = \Auth::user();
        $notificationService = new NotificationService($admin);
        $lastCreatedAt = null;
        if ($request->filled('last_created_at')) {
          $lastCreatedAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('last_created_at'));
        }
        $notifications = $notificationService->get($lastCreatedAt);

        return response()->success('', compact('notifications'));
    }

    public function read()
    {
        /** @var Admin $admin */
        $admin = \Auth::user();
        $notificationService = new \Shetabit\Shopit\Modules\Core\Services\NotificationService($admin);
        $notificationService->read();

        return response()->success('');
    }



}
