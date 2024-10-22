<?php

namespace Modules\Core\Services\Media;

use Modules\Core\Entities\Media;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Variety;

class MediaDisplay
{
    public function __construct(
        public int $id,
        public string $url,
        public array $conversions,
    ) {}


    public static function objectCreator(Media $media): MediaDisplay
    {
        $conversions = [];
        $extension = $media->extension === 'webp' ? 'jpg' : $media->extension;
        if (in_array($media->model_type, [Product::class, Variety::class])) {
            $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $media->file_name);

            foreach (['lg', 'md', 'sm'] as $x) {
                $conversions[$x] = url(
                    'storage/' . $media->uuid . '/conversions/'
                    . $withoutExt. '-thumb_' . $x . '.' . $extension
                );
            }
        }

        return new MediaDisplay(
            $media->id,
            $media->getUrl(),
            $conversions,
        );

    }

}
