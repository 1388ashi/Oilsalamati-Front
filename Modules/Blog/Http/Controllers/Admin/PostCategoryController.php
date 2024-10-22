<?php

namespace Modules\Blog\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Blog\Entities\PostCategory;
use Modules\Blog\Http\Requests\Admin\PostCategory\PostCategoryStoreRequest;
use Modules\Blog\Http\Requests\Admin\PostCategory\PostCategoryUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Shetabit\Shopit\Modules\Blog\Http\Controllers\Admin\PostCategoryController as BasePostCategoryController;

class PostCategoryController extends Controller
{
	public function index(): JsonResponse|View
	{
		$postCategories = PostCategory::filters()
			->select(['id', 'name', 'status', 'order', 'created_at'])
			->latest('id')
			->paginate();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('Get all post categories', compact('postCategories'));
		}

		return view('blog::admin.post-category.index', compact('postCategories'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param PostCategoryStoreRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(PostCategoryStoreRequest $request): RedirectResponse|JsonResponse
	{
		$postCategory = PostCategory::create($request->all());
        ActivityLogHelper::storeModel('دسته بندی مطلب ثبت شد', $postCategory);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('دسته بندی مطلب با موفقیت ثبت شد.', compact('postCategory'));
		}

		return redirect()->back()->with('success', 'دسته بندی مطلب با موفقیت ثبت شد');
	}

	/**
	 * Show the specified resource.
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id): JsonResponse
	{
		$postCategory = PostCategory::findOrFail($id);

		return response()->success('دسته بندی مطلب با موفقیت دریافت شد.', compact('postCategory'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param PostCategoryUpdateRequest $request
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(PostCategoryUpdateRequest $request, $id): RedirectResponse|JsonResponse
	{
		$postCategory = PostCategory::findOrFail($id);

		$postCategory->update($request->all());
        ActivityLogHelper::updatedModel('دسته بندی مطلب بروز شد', $postCategory);


		if (request()->header('Accept') == 'application/json') {
			return response()->success('دسته بندی مطلب با موفقیت به روزرسانی شد.', compact('postCategory'));
		}

		return redirect()->back()->with('success', 'دسته بندی مطلب با موفقیت به روزرسانی شد');
	}

	/**
	 * Remove the specified resource from storage.
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id): RedirectResponse|JsonResponse
	{
		$postCategory = PostCategory::findOrFail($id);

		$postCategory->delete();
        ActivityLogHelper::deletedModel('دسته بندی مطلب حذف شد', $postCategory);


		if (request()->header('Accept') == 'application/json') {
			return response()->success('دسته بندی مطلب با موفقیت حذف شد.');
		}
		
		return redirect()->back()->with('success', 'دسته بندی مطلب با موفقیت حذف شد');
	}
}
