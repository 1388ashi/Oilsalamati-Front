<?php

namespace Modules\Attribute\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Http\Requests\Admin\AttributeStoreRequest;
use Modules\Attribute\Http\Requests\Admin\AttributeUpdateRequest;
//use Shetabit\Shopit\Modules\Attribute\Http\Controllers\Admin\AttributeController as BaseAttributeController;

class AttributeController extends Controller
{
    public function index(): JsonResponse|View
    {
        $attributes = Attribute::latest('id')->filters()->paginate();
        $types = Attribute::getAvailableType();

        if (request()->header('Accept') == 'application/json') {
            return response()->success('دریافت لیست ویژگی ها', compact('attributes'));
        }
        return view('attribute::admin.index', compact('attributes', 'types'));
    }
    public function create()
    {
        $attributes = Attribute::latest('id')->filters()->paginate();
        $types = Attribute::getAvailableType();

        return view('attribute::admin.create', compact('attributes', 'types'));
    }

    public function store(AttributeStoreRequest $request)
    {
        $attribute = Attribute::create($request->all());
        ActivityLogHelper::storeModel('ویژگی ثبت شد', $attribute);

        if ($request->type === 'select' && $request->values) {
            foreach ($request->values as $value) {
                $attribute->values()->create([
                    'value' => $value
                ]);
            }
        }
        
        if (request()->header('Accept') == 'application/json') {
            $attribute->load('values');
            return response()->success('ویژگی با موفقیت ثبت شد', compact('attribute'));
        }
        return redirect()->route('admin.attributes.index')->with([
            'success' => 'ویژگی با موفقیت ثبت شد'
        ]);
    }

    public function edit($id)
    {
        $attribute = Attribute::findOrfail($id);
        $types = Attribute::getAvailableType();

        return view('attribute::admin.edit', compact('attribute', 'types'));
    }
    public function show($id): JsonResponse
    {
        $attribute = Attribute::findOrfail($id);

        return response()->success('ویژگی با موفقیت دریافت شد', compact('attribute'));
    }

    public function update(AttributeUpdateRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());
        ActivityLogHelper::updatedModel('ویژگی بروز شد', $attribute);

        if ($attribute->type === 'select') {
            foreach ($request->input('values', []) as $value) {
                // If already exists don't add
                if (!$attribute->values()->where('value', $value)->exists()) {
                    $attribute->values()->create([
                        'value' => $value
                    ]);
                }
            }

            // مقادیری که ویرایش شده
            foreach ($request->input('edited_values', []) as $editedValue) {
                $attributeValue = $attribute->values()->find($editedValue['id']);
                if (!$attributeValue) {
                    continue;
                }
                $attributeValue->value = $editedValue['value'];
                $attributeValue->save();
            }

            // مقادیری که حذف شده
            foreach ($request->input('deleted_values', []) as $editedValue) {
                $attributeValue = $attribute->values()->find($editedValue['id']);
                if (!$attributeValue) {
                    continue;
                }
                $attributeValue->delete();
            }
        }
        
        if (request()->header('Accept') == 'application/json') {
            return response()->success('ویژگی با موفقیت به روزرسانی شد', compact('attribute'));
        }
        return redirect()->route('admin.attributes.index')->with([
            'success' => 'ویژگی با موفقیت به روزرسانی شد'
        ]);
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        ActivityLogHelper::deletedModel('ویژگی حذف شد', $attribute);

        if (request()->header('Accept') == 'application/json') {
            return response()->success('ویژگی با موفقیت حذف شد', compact('attribute'));
        }
        
        return redirect()->route('admin.attributes.index')->with([
            'success' => 'ویژگی با موفقیت حذف شد'
        ]);
    }
}
