<?php

class SaltedCropperImageExt extends DataExtension
{
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'isCropped'     =>  'Boolean'
    ];

    public function getRatio()
    {
        return (($this->owner->Width / $this->owner->Height) * 100) . '%';
    }
}
