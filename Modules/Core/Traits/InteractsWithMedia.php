<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\File;
use Shetabit\Shopit\Modules\Core\Traits\InteractsWithMedia as BaseInteractsWithMedia;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;



    public function scopeWithSpatieMediaFields($query, array $select_list = [], $collection_names = [])
    {
        /*
         | Method Description:
         |
         | this scope written for getting spatie media files with necessary fields. of course, you can add more fields with select_list array
         | if we want all collections for this model we return all but if we want a specific collection name we return jus that picture
         | at first we set without('media') because spatie media get media of a model's media automatically and its
         | handle might be uncontrollable. so for control all methods correctly we set without('media') at first then
         | we get its fields by with method in query.
         |
         * */

        // in select_list array some fields are very necessary. so we have to get them by default
        array_push($select_list, 'id', 'model_type', 'model_id', 'collection_name', 'mime_type', 'disk', 'file_name', 'conversions_disk');
        // if collection_name was empty it means that we want all pictures of all collections


        if (count($collection_names) == 0) {
            return $query->without('media')->with([
                'media' => function ($query) use ($select_list) {
                    $query->select($select_list);
                },
            ]);
        }
        // so we want some specific collection names
        return $query->without('media')->with([
            'media' => function ($query) use ($select_list, $collection_names) {
                $query->whereIn('collection_name', $collection_names)->select($select_list);
            },
        ]);
    }


    public function saveFileSpatieMedia($file, string $collection): void
    {
        /*
         * Method Description:
         *
         * this method is for save a file in spatie media library with specific collection.
         * and also it has specific file name for each model, and it has a specific fileName.
         * */
        if ($collection == null) {
            // $collection = self::getMainCollection();
        } // we can't call a method in function inputs
        $modelName = new \ReflectionClass($this);
        $modelID = $this->id;

        // store file with specific name
        // $extension = $file->getClientOriginalExtension();
        $this->addMedia($file)
            // ->sanitizingFileName(function ($fileName) use ($extension, $modelName, $modelID, $collection) { /* this function is for change file name */
            //     return
            //         now()->format('Ymd_His_') .
            //         ucfirst($modelName->getShortName()) . "Model" . "_" .
            //         "ID" . $modelID .
            //         "_Collection_" . $collection . "_" .
            //         str::random(10)/* . '_' . str_replace(['#', '/', '\\', ' ', '$'], '-', $fileName)*/
            //         . "." . $extension;
            // })
            ->toMediaCollection($collection);
    }


    public function storeFiles($images, $collection_name): void
    {
        if (!$images)
            return;
        if (!is_array($images)) {
            $images = [$images];
        }
        foreach ($images as $image) {
            $this->saveFileSpatieMedia($image, $collection_name);
        }
    }

    public function updateFiles($images, $collection_name, $deleteOlders = true): void
    {
        if (!$images)
        return;

        if (!is_array($images)) {
            $images = [$images];
        }

        if ($deleteOlders)
        $this->media()->where('collection_name', $collection_name)->delete();

        foreach ($images as $image) {
            $this->saveFileSpatieMedia($image, $collection_name);
        }
    }
}
