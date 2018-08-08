<?php

namespace SaltedHerring\Salted\Cropper\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Assets\Image_Backend;

class SaltedCroppableImageExtension extends Extension
{
    public function crop($x, $y, $w, $h)
    {
        $variant = $this->owner->variantName(__FUNCTION__, $x, $y, $w, $h);
        return $this->owner->manipulateImage($variant, function (Image_Backend $backend) use($x, $y, $w, $h) {
            $clone = clone $backend;
            $resource = clone $backend->getImageResource();
            $resource->crop( $w , $h , $x , $y );
            $clone->setImageResource($resource);
            return $clone;
        });
    }
}
