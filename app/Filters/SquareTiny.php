<?php

namespace App\Filters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class SquareTiny implements FilterInterface
{
    public function applyFilter(Image $image)
    {
        if ($image->width() >= 300) {
            $image->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $image->resizeCanvas(200, 200, 'center', false, [255, 255, 255, 0]);
        $image->resize(50, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        return $image->encode('jpg', config('settings.jpeg_quality'));
    }
}
