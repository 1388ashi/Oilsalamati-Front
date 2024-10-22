<?php

namespace Modules\Blog\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
//use Modules\Core\Entities\BaseModel;
//use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Exceptions\ModelCannotBeDeletedException;
use Modules\Core\Traits\HasAuthors;
//use Shetabit\Shopit\Modules\Blog\Entities\PostCategory as BasePostCategory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class PostCategory extends Model implements Sortable
{
    use HasAuthors, /*HasCommonRelations, */SortableTrait;

    protected $fillable = [
        'name', 'slug', 'status'
    ];

    protected $hidden = ['created_at', 'updated_at','creator_id','updater_id'];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected static $commonRelations = [
        /*'posts'*/
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    public static function booted()
    {
        static::deleting(function (PostCategory $postCategory) {
            if ($postCategory->countPosts() > 0) {
                throw new ModelCannotBeDeletedException('این دسته بندی دارای مطلب می باشد و نمی تواند حذف شود.');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    //Relations

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'post_category_id');
    }

    public function isDeletable(): bool
    {
        return $this->countPosts() === 0;
    }

    public static function getActiveCategories()
    {
        return PostCategory::query()->select('id', 'name', 'status')->active()->get();
    }

    public static function getAllCategories()
    {
        return PostCategory::query()->select('id', 'name', 'status')->get();
    }

    public function countPosts(): Int
    {
        return $this->posts()->count();
    }

    public function scopeFilters($query)
    {
        $status = request('status');

        return $query
            ->when(request('id'), function (Builder $query) {
                $query->where('id', request('id'));
            })
            ->when(request('title'), function (Builder $query) {
                $query->where('title', 'LIKE', '%' . request('title') . '%');
            })
            ->when(isset($status), fn($query) => $query->where("status", $status))
            ->when(request('start_date'), function (Builder $query) {
                $query->whereDate('created_at', '>=', request('start_date'));
            })
            ->when(request('end_date'), function (Builder $query) {
                $query->whereDate('created_at', '<=', request('end_date'));
            });
    }
}
