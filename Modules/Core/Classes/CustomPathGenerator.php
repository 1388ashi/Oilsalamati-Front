<?php

namespace Modules\Core\Classes;

//use Shetabit\Shopit\Modules\Core\Classes\CustomPathGenerator as BaseCustomPathGenerator;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class CustomPathGenerator extends DefaultPathGenerator
{
    protected function getBasePath(Media $media): string
    {
        return $media->uuid;
    }
}
