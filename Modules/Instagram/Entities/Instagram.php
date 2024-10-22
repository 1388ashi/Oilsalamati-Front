<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\Media;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Setting\Entities\Setting;
use Shetabit\Shopit\Modules\Instagram\Entities\Instagram as BaseInstagram;
use Spatie\MediaLibrary\HasMedia;

//class Instagram extends BaseInstagram
class Instagram extends Model implements HasMedia
{
    function getJsonContent(): string
    {
        return '[' . file_get_contents(__DIR__ . '/i.json') . ']';
    }



    // came from vendor ================================================================================================

    use InteractsWithMedia;

    protected $table = 'instagram_posts';

    protected $hidden = ['media'];

    protected $appends = ['image'];

    protected $fillable = [
        'link',
        'likes',
        'comments'
    ];

    public static function getForceInstagramPosts()
    {
        \Cache::forget('instagram_posts');
        return static::getInstagramPosts();
    }

    public static function getInstagramPosts()
    {
        return \Cache::remember('instagram_posts', 1, function () {
            return static::query()->take(12)->paginate(12);
            $instagram = new static;
            $posts = $instagram->getPostsInInstagram();
            $oldPosts = static::query()->take(12)->get();
            if (!empty($posts) && (count($posts) >= count($oldPosts))) {
                try {
                    try {
                        \DB::beginTransaction();
                        $table = \DB::table('instagram_posts');
                        $table->truncate();
                        $instagram->storePosts($posts);
                        \DB::commit();
                    } catch (\Error $exception) {

                        if (DB::getRawPdo()->inTransaction()) {
                            \DB::rollBack();
                        }
                    }
                } catch (\Exception $exception) {
                    if (DB::getRawPdo()->inTransaction()) {
                        \DB::rollBack();
                    }
                }
            }

            return static::query()->take(12)->paginateOrAll(12)->toArray();
        });
    }

    public function getPostsInInstagram()
    {
        $instagram = $this->fetchDetailsInstagram();
        return isset($instagram[0]) && isset($instagram[0]['graphql']) ? $instagram[0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] : [];
    }

    public function fetchDetailsInstagram()
    {
//        $username = $this->getUsername();
//        if (is_null($username)) return null;
//        $url = "https://www.instagram.com/{$username}";
//
//
//        /*
//         * Get from cookie
//         */
//        $authUsername = 'sh._._._b';
//        $sessionId = '48721333113%3A43BzULU9V3EPWg%3A27';
//        $ig_did = '9FCE92F4-9D10-4232-A6D8-3357684271D1';
//        $db_user_id = '48721333113';
//        $csrftoken = 'wsOpbDrgbLdV6p81IVkM8QFbWSTCIL61';
//
//
//        try {
//            $client =  Http::withHeaders([
//                'Accept' => 'application/json',
//                'Content-Type' => 'application/json',
//                'Cookie' => 'ds_user='.$authUsername.'; ig_did=' . $ig_did
//                 .'; db_user_id=' . $db_user_id . '; sessionid=' . $sessionId
//                    .'; csrftoken=' . $csrftoken,
//                'User-Agent: Instagram 7.16.0 Android'
//            ])->get($url, [
//                '__a' => 1
//            ]);
//            $response = '['.$client->body().']';
//            $response = json_decode($response, true);
//        } catch (\Exception $exception) {
//            $response = [];
//        }


        $response = $this->getJsonContent();
        $response = json_decode($response, true);

        return $response;
    }

    public function storePosts($posts)
    {
        $i = 0;
        foreach ($posts as $post) {
            $image = $post['node']['display_url'];
            $commentCount = $post['node']['edge_media_to_comment']['count'];
            $likeCount = $post['node']['edge_liked_by']['count'];
            $linkPost = 'https://www.instagram.com/p/' . $post['node']['shortcode'];
            $postsArray = [
                'image' => $image,
                'comment_count' => $commentCount,
                'like_count' => $likeCount,
                'link_post' => $linkPost,
            ];
            $this->storeModel($postsArray);

            $i++;
            if ($i == 12) {
                break;
            }
        }
    }

    public function storeModel($posts)
    {
        $model = static::query()->create([
            'link' => $posts['link_post'],
            'likes' => $posts['like_count'],
            'comments' => $posts['comment_count'],
        ]);
        $image = Http::get($posts['link_post'] . '/media/?size=m');
        $imageObj = Image::make($image->body());
        $path = storage_path('app/temp');
        if (!is_dir($path)) {
            mkdir($path, 0755);
        }
        $imgPath = $path . '/' . time() . '.jpg';
        $imageObj->save($imgPath);
        $model->addImage($imgPath);
        File::delete($imgPath);

        return $model;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }

    public function addImage($url)
    {
        return $this->addMedia($url)
            ->toMediaCollection('images');
//        return $this->addMediaFromString($url)
//            ->toMediaCollection('images');
        if (is_string($url) && str_contains($url, 'http')) {
            return $this->addMediaFromUrl($url)
                ->toMediaCollection('images');
        } else {
            return $this->addMedia($url)
                ->toMediaCollection('images');
        }

    }

    public function getImageAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('images');
        if (!$media) return null;
        return MediaDisplay::objectCreator($media);
    }

    public function getUsername()
    {
        $username = Setting::query()->select(['name', 'value'])->where('name', 'instagram')
            ->first();

        return $username && $username->exists() ? $username->value : null;
    }
}
