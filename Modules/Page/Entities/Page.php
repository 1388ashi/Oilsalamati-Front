<?php

namespace Modules\Page\Entities;

//use Shetabit\Shopit\Modules\Page\Entities\Page as BasePage;

//use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;

class Page extends Model
{
//    use Sluggable;

    protected $fillable = [
        'title', 'text', 'slug'
    ];

    public function sluggable(): array
    {
        $slug = empty($this->slug) ? 'title' : 'slug';

        return [
            'slug' => [
                'source' => $slug
            ]
        ];
    }

}
