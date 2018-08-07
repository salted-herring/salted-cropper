<?php

namespace SaltedHerring\Salted\Cropper\Extensions;

class SaltedCropperImageExt extends DataExtension
{
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'isCropped'     =>  'Boolean'
    ];
}
