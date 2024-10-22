<?php

namespace Modules\Comment\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Comment\Entities\Comment;
//use Shetabit\Shopit\Modules\Comment\Http\Requests\Admin\CommentAnswerRequest as BaseCommentAnswerRequest;

class CommentAnswerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body' => 'required|string',
            'status' => 'required|in:' . implode(',', Comment::getAvailableStatuses())
        ];
    }

    public function prepareForValidation()
    {
        $this->merge(['status' => 'approved']);
    }
}
