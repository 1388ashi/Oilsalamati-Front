<?php

namespace Modules\Comment\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Comment\Entities\Comment;
//use Shetabit\Shopit\Modules\Comment\Http\Requests\Admin\CommentUpdateRequest as BaseCommentUpdateRequest;

class CommentUpdateRequest extends FormRequest
{
    private $nameRequired;
    private $emailRequired;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [$this->nameRequired ? 'required' : 'nullable', 'string'],
            'email' => [$this->emailRequired ? 'required' : 'nullable', 'string'],
            'body' => 'required|string',
            'status' => 'required|in:' . implode(',', Comment::getAvailableStatuses())
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $comment = Comment::findOrFail($this->route('comment'));

        if ($comment->creator) {
            $this->request->remove('name');
            $this->request->remove('email');
            $this->nameRequired = false;
            $this->emailRequired = false;
        }
    }
}
