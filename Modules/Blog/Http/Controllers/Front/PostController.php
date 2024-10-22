<?php

namespace Modules\Blog\Http\Controllers\Front;


use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Advertise\Entities\Advertise;
use Modules\Blog\Entities\Post;
use Modules\Blog\Entities\PostCategory;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Classes\Tag;
use Modules\Product\Entities\Product;

//use Shetabit\Shopit\Modules\Blog\Http\Controllers\Front\PostController as BasePostController;
//use Shetabit\Shopit\Modules\Blog\Services\BlogService;

class PostController extends Controller
{
    public function index($category_id = null)
    {
        $posts = Post::published()
            ->with(['category' => function ($query) {
                $query->select(['id', 'name', 'slug']);
            }])->when($tagName = request('tag'), function ($query) use ($tagName) {
                $query->whereHas('tags', function ($query) use ($tagName) {
                    $query->where('name', $tagName);
                });
            })->when($categoryName = request('category'), function ($query) use ($categoryName) {
                $query->whereHas('category', function ($query) use ($categoryName) {
                    $query->where('name', $categoryName);
                });
            })->when(request('title'), function ($query) {
                $query->where('title', 'LIKE', "%" . request('title') ."%");
            })->when($category_id, function ($query) use ($category_id) {
                $query->where('post_category_id', $category_id);
            })
            ->select(['id', 'title', 'summary', 'slug', 'special', 'created_at', 'post_category_id','published_at'])
//            ->withCount('views')
            ->filters()
            ->orderBy('pin', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        $data = [
            'posts' => $posts
        ];

        if (!request('posts_only')) {
//            $mostViews = BlogService::getMostViews();
            $category  = PostCategory::query()->active()->get();
//            $banner = Advertise::getForHome();

            $data = array_merge([
                'category' => $category,
//                'banner' => $banner,
//                'mostViews' => $mostViews
            ], $data);

            $coreSetting = app(CoreSettings::class);
            if (in_array('tags', $coreSetting->get('blog.front', []))) {
                $tagIds = DB::table('taggables')->select('tag_id')->take(15)
                    ->distinct('tag_id')->where('taggable_type', Post::class)->get()
                    ->map(function ($taggable) {
                        return $taggable->tag_id;
                    });

                $data['tags'] = Tag::query()->whereIn('id', $tagIds)->get();
            }
        }

        return response()->success('', $data);
    }

    public function show($id)
    {
        $user = auth()->user();
        $getPost = Post::query()->published()->with([
            'products' => function ($query) {
                return $query->select(Product::SELECTED_COLUMNS_FOR_FRONT);
            }
        ])->findOrFail($id);
        foreach ($getPost->products as $product) {
            $product->setAppends(Product::APPENDS_LIST_FOR_FRONT);
        }

        $getPost->setAttribute('view_count', views($getPost)->count());
        views($getPost)->record();

        if ($getPost->status != Post::STATUS_PUBLISHED || Carbon::now()->lt($getPost->published_at)) {
            return response()->error('دسترسی به این مطلب امکان پذیر نیست', [], 403);
        }

        $category  = PostCategory::query()->active()->get();
        $suggests  = Post::query()->published()
//            ->withCount('views')
            ->with(['category'])
            ->where('post_category_id',  $getPost->post_category_id)
            ->where('id', '!=' ,$getPost->id)
            ->inRandomOrder()->take(3)->get();

        $lastPost  = Post::query()->select(['id', 'title', 'slug', 'created_at'])->published()
            ->where('id', '!=', $getPost->id)/*->withCount('views')*/->latest()->take(10)->get();
        $banner = Advertise::getForHome();

        $post = [
            'post' => $getPost,
            'category' => $category,
            'suggests' => $suggests,
            'lastPost' => $lastPost,
            'user' => $user,
            'banner' => $banner
        ];

        return response()->success('', compact('post'));
    }




    public function byCategory($category_id): \Illuminate\Http\JsonResponse
    {
        return $this->index($category_id);
    }
}
