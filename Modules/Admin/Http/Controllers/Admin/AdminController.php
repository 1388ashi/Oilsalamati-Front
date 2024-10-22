<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Entities\Admin;
use Modules\Admin\Http\Requests\AdminStoreRequest;
use Modules\Admin\Http\Requests\AdminUpdateRequest;
use Illuminate\Contracts\Support\Renderable;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Core\Helpers\Helpers;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admin::query()->with('roles.permissions');
        
        if (request()->header('Accept') == 'application/json') {

          Helpers::applyFilters($admins);
          $admins = Helpers::paginateOrAll($admins);

          return response()->success('لیست ادمین ها', compact('admins'));
        }

        $admins = $admins->latest('id')->paginate();
        
        return view('admin::admin.index', compact('admins'));
    }
    public function create(): Renderable
    {
        $roles = Role::select('id','name','label')->get();

        return view('admin::admin.create', compact('roles'));
    }
    /**
     * Store a newly created resource in storage.
     * @param AdminStoreRequest $request
     */
    public function store(AdminStoreRequest $request)
    {
      $admin = Admin::query()->create($request->all());
      $role =Role::findOrFail($request->role);

      $admin->assignRole($role);

      ActivityLogHelper::simple('ادمین ساخته شد', 'store', $admin);

      if (request()->header('Accept') == 'application/json') {
        return response()->success("ادمین با موفقیت ساخته شد.", compact('admin'));
      }
      return redirect()->route('admin.admins.index')
      ->with('success', 'ادمین با موفقیت ثبت شد.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     */
    public function show($id)
    {
        $admin = Admin::query()->with('roles.permissions')->findOrFail($id);

        return response()->success('', compact('admin'));
    }
    public function edit(Admin $admin): Renderable
    {
        $adminRolesName = $admin->getRoleNames()->first();

        if ($adminRolesName == 'super_admin') {
            $roles = Role::select('id','name','label')->where('name','super_admin')->get();
        }else{
            $roles = Role::select('id', 'name', 'label')->where('name', '!=', 'super_admin')->get();
        }
        return view('admin::admin.edit', compact('roles','adminRolesName','admin'));
    }
    /**
     * Update the specified resource in storage.
     * @param AdminUpdateRequest $request
     * @param int $id
     */
    public function update(AdminUpdateRequest $request,Admin $admin)
    {
      $password = filled($request->password) ? $request->password : $admin->password;

      $admin->update([
          'name' => $request->name,
          'username' => $request->username,
          'mobile' => $request->mobile,
          'password' => Hash::make($password),
      ]);
      $role =Role::findOrFail($request->role);
      $admin->assignRole($role);
      ActivityLogHelper::updatedModel('ادمین ویرایش شد', $admin);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('باموفقیت بروزرسانی شد', compact('admin'));
        }
        return redirect()->route('admin.admins.index')
        ->with('success', 'ادمین با موفقیت به روزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy($id)
    {
        $admin = Admin::query()->findOrFail($id);
        if ($admin->hasRole('super_admin')){
            return throw Helpers::makeValidationException('شما مجاز به حذف سوپر ادمین نمیباشید', 'role');
        }
        $admin->delete();
        ActivityLogHelper::updatedModel('ادمین حذف شد', $admin);

        if (request()->header('Accept') == 'application/json') {
          return response()->success('باموفقیت حذف شد', compact('admin'));
        }
        return redirect()->route('admin.admins.index')
        ->with('success', 'ادمین با موفقیت حذف شد.');
    }
}
