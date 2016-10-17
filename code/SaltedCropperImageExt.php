<?php

class SaltedCropperImageExt extends DataExtension {
    protected static $has_one = array(
        'SaltedCroppedImage'  =>  'Image'
    );

    public function onBeforeDelete() {
        parent::onBeforeDelete();
        if (!empty($this->owner->SaltedCroppedImageID)) {
            $this->owner->SaltedCroppedImage()->delete();
        }
    }
}
