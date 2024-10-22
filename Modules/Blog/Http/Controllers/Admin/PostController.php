<?php

namespace Modules\Blog\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Blog\Entities\Post;
use Modules\Blog\Entities\PostCategory;
use Modules\Blog\Http\Requests\Admin\Post\PostStoreRequest;
use Modules\Blog\Http\Requests\Admin\Post\PostUpdateRequest;
use Modules\Core\Http\Controllers\BaseController;
//use Shetabit\Shopit\Modules\Blog\Http\Controllers\Admin\PostController as BasePostController;
use Shetabit\Shopit\Modules\Blog\Jobs\PostDestroyJob;
use Shetabit\Shopit\Modules\Blog\Jobs\PostIndexJob;
use Shetabit\Shopit\Modules\Blog\Jobs\PostShowJob;
use Shetabit\Shopit\Modules\Blog\Jobs\PostStoreJob;
use Shetabit\Shopit\Modules\Blog\Jobs\PostUpdateJob;
use Exception;

class PostController extends BaseController
{
	public function store(PostStoreRequest $request)
	{
		try {
            $category = PostCategory::findOrFail($request->post_category_id);
            $post = $category->posts()->create($request->all());
            //tags
            if ($request->tags) {
                $post->attachTags($request->tags);
            }
            //media
            if ($request->hasFile('image')) {
                $post->addImage($request->image);
            }
			if ($request->product_ids) {
				$post->products()->attach($request->product_ids);
			}
            ActivityLogHelper::storeModel('مطلب ثبت شد', $post);
			if (request()->header('Accept') == 'application/json') {
				return response()->success('مطلب با موفقیت ثبت شد', compact('post'));
			}
			return redirect()->route('admin.posts.index')->with('success', 'مطلب با موفقیت ثبت شد');
		} catch (Exception $exception) {
			Log::error($exception->getTraceAsString());
			if (request()->header('Accept') == 'application/json') {
				return response()->error('مشکلی در برنامه رخ داده است:' . $exception->getMessage(), $exception->getTrace(), 500);
			}
			return redirect()->back()->withInput()->with('error', 'مشکلی در برنامه رخ داده است');
		}
	}


	public function update(PostUpdateRequest $request, $id)
	{
		try {
			$post = $this->run(PostUpdateJob::class, [
				'model' => Post::class,
				'categoryModel' => PostCategory::class, 'id' => $id
			]);

			if ($request->product_ids) {
				$post->products()->sync($request->product_ids);
			}
            ActivityLogHelper::updatedModel('مطلب بروز شد', $post);

			if (request()->header('Accept') == 'application/json') {
				return response()->success('مطلب با موفقیت به روزرسانی شد', compact('post'));
			}
			return redirect()->route('admin.posts.index')->with('success', 'مطلب با موفقیت به روزرسانی شد');
		} catch (Exception $exception) {
			Log::error($exception->getTraceAsString());
			if (request()->header('Accept') == 'application/json') {
				return response()->error('مشکلی در برنامه رخ داده است:' . $exception->getMessage(), $exception->getTrace(), 500);
			}
			return redirect()->back()->withInput()->with('error', 'مشکلی در برنامه رخ داده است');
		}
	}




	// came from vendor ================================================================================================
	public function index()
	{
		$posts = Post::query()->latest('id')
		->withCount('views')->filters()->paginate();

		if (request()->header('Accept') == 'application/json') {
			return response()->success('Get all posts', compact('posts'));
		}

		$categories = PostCategory::getAllCategories();

		return view('blog::admin.post.index', compact(['posts', 'categories']));
	}

	public function show($id)
	{
		$post = Post::findOrFail($id);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مطلب با موفقیت دریافت شد', compact('post'));
		}

		return view('blog::admin.post.show', compact(['post']));
	}

	public function create()
	{
		$statuses = Post::getAvailableStatuses();
		$categories = PostCategory::getActiveCategories();

		return view('blog::admin.post.create', compact(['statuses', 'categories']));
	}

	public function edit(Post $post)
	{
		$statuses = Post::getAvailableStatuses();
		$categories = PostCategory::getActiveCategories();
		$postProducts = $post->products;
		$post->loadCommonRelations();

		return view('blog::admin.post.edit', compact(['statuses', 'categories', 'post', 'postProducts']));
	}

	public function destroy($id)
	{
		$post = Post::findOrFail($id);
        $post->delete();
        ActivityLogHelper::deletedModel('مطلب حذف شد', $post);

		if (request()->header('Accept') == 'application/json') {
			return response()->success('مطلب با موفقیت حذف شد');
		}

		return redirect()->back()->with('success', 'مطلب با موفقیت حذف شد');
	}
}
