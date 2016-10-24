<?php

class SaltedCropperImageExt extends DataExtension {
	
	protected static $db = array(
		'ContainerX'			=>	'Int',
		'ContainerY'			=>	'Int',
		'ContainerWidth'		=>	'Int',
		'ContainerHeight'		=>	'Int',
		'CropperX'				=>	'Int',
		'CropperY'				=>	'Int',
		'CropperWidth'			=>	'Int',
		'CropperHeight'			=>	'Int'
	);
	
    protected static $has_one = array(
        'SaltedCroppedImage'	=>	'Image'
    );

    public function onBeforeDelete() {
        parent::onBeforeDelete();
        if (!empty($this->owner->SaltedCroppedImageID)) {
            $this->owner->SaltedCroppedImage()->delete();
        }
    }
}
