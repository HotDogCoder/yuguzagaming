<?php

namespace App\Filters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class Picture implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        if ($image->width() >= 1500) {
            $image->resize(1500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        // insert watermark
        $image->insert(asset('img/watermark.png'), 'bottom-right', 10, 10);

        return $image->encode('jpg', config('settings.jpeg_quality'));
    }
}
