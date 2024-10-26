<?php

namespace Modules\Comment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Modules\Blog\Entities\Post;
use Modules\Comment\Entities\Comment;
use Modules\Comment\Http\Requests\CommentStoreRequest;
use Modules\Core\Helpers\Helpers;
//use Shetabit\Shopit\Modules\Comment\Http\Controllers\CommentController as BaseCommentController;

class CommentController extends Controller
{
    public function index(Model $model)
    {
        $comments = $model->comments()->active()->latest()->get();

        return response()->success('', compact('comments'));
    }

    public function store(Post $post, CommentStoreRequest $request)
    {
        // ممکنه نال باشه
        $parent = Comment::active()->find($request->parent_id);
        if ($parent && $parent->parent_id) {
            return response()->error('امکان پاسخگویی به جواب وجود ندارد', [], 400);
        }
        if ($request->has('name') && $request->has('email')) {
            $comment = $post->comment(request()->all(), null, $parent);
        } else {
            $comment = $post->comment(request()->all(), Helpers::getAuthenticatedUser(), $parent);
        }
        $comment->setRelation('children', collect());

        $data = [
            'status' => 'success',
            'message' => 'نظر با موفقیت ثبت شده و پس از تایید نمایش داده خواهد شد'
        ];
        return redirect()->back()->with($data);
    }
}
