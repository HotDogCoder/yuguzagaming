<?php

namespace App\Filters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class AvatarSquare implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        if ($image->width() > $image->height()) {
            $image->resize(null, 200, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $image->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $image->resizeCanvas(200, 200, 'center', false, [255, 255, 255, 0]);

        return $image->encode('jpg', config('settings.jpeg_quality'));
    }
}
