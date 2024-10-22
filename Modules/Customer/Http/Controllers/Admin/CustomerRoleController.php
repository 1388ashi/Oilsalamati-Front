<?php

namespace Modules\Customer\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Customer\Entities\CustomerRole;
use Shetabit\Shopit\Modules\Customer\Http\Requests\Admin\CustomerRoleStoreRequest;
use Shetabit\Shopit\Modules\Customer\Http\Requests\Admin\CustomerRoleUpdateRequest;

class CustomerRoleController extends Controller
{
    public function index()
    {
        $customer_roles = CustomerRole::query();
        $customer_roles = $customer_roles->latest('id')->filters()->paginateOrAll();

        return response()->success('دریافت لیست همه نقش مشتری ها', compact('customer_roles'));
    }

    public function store(CustomerRoleStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $customer_role = CustomerRole::query()->create($request->all());

        return response()->success('نقش مشتری با موفقیت ایجاد شد.', compact('customer_role'));
    }

    public function update(CustomerRoleUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $customer_role = CustomerRole::query()->findOrFail($id);
        $customer_role->update($request->all());

        return response()->success('نقش مشتری با موفقیت بروزرسانی شد.', compact('customer_role'));
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $customer_role = CustomerRole::query()->findOrFail($id);
        $customer_role->delete();

        return response()->success('نقش مشتری با موفقیت حذف شد.', compact('customer_role'));
    }
}
