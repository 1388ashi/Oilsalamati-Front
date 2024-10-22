<?php

namespace Modules\Comment\Entities;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Admin\Entities\Admin;
use Modules\Core\Classes\DontAppend;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\BaseModel as Model;
use Illuminate\Database\Eloquent\Model;
//use Shetabit\Shopit\Modules\Comment\Entities\Comment as BaseComment;

class Comment extends Model
{
    use NodeTrait;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_UNAPPROVED = 'unapproved';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $with = ['children', 'creator'];

    protected $appends = ['children_count'];

    public static function getAvailableStatuses()
    {
        return [
            static::STATUS_PENDING, static::STATUS_APPROVED, static::STATUS_UNAPPROVED
        ];
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function children()
    {
        $relation = parent::hasMany(get_class($this), $this->getParentIdName())
            ->setModel($this);
        if (!(Auth::user() instanceof Admin)) {
            $relation->active();
        }

        return $relation;
    }

    protected function getArrayableRelations()
    {
        $result = parent::getArrayableRelations();

        return $result;
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }


    public function creator(): MorphTo
    {
        return $this->morphTo('creator');
    }

    /**
     * Create a comment and persists it.
     *
     * @param Model $commentable
     * @param array $data
     * @param Model|null $creator
     *
     * @return static
     */
    public function createComment(Model $commentable, array $data, $creator = null): self
    {
        if ($creator) {
            $data = array_merge($data, [
                'creator_id'   => $creator->getAuthIdentifier(),
                'creator_type' => $creator->getMorphClass(),
                'name' => $creator->name,
                'email' => $creator->email
            ]);
        }

        return $commentable->comments()->create($data);
    }

    /**
     * Update a comment by an ID.
     *
     * @param int $id
     * @param array $data
     *
     * @return Model
     */
    public function updateComment(int $id, array $data): Model
    {
        $comment = static::find($id);
        $comment->fill($data);
        $comment->saveOrFail();

        return $comment;
    }

    /**
     * Delete a comment by an ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteComment(int $id): bool
    {
        return (bool) static::find($id)->delete();
    }

    /**
     * @return Builder
     */
    public static function scopeActive($query)
    {
        return $query->where('status', '=', static::STATUS_APPROVED);
    }

    public function getChildrenCountAttribute($value = null)
    {
        if (!$this->relationLoaded('children')) {
            return new DontAppend('getChildrenCountAttribute');
        }

        $count = count($this->children);
        foreach ($this->children as $child) {
            $count += $child->getChildrenCountAttribute();
        }

        return $count;
    }
}
