<?php

class SaltedUploader extends UploadField {
    private $Ratio = 1;
    public function __construct($name, $title = null, SS_List $items = null) {
        parent::__construct($name, $title);
    }

    public function Field($properties = array()) {
        $field = parent::Field($properties);
        Requirements::combine_files(
            'cropperfield-all.css',
            array(
                SALTEDCROPPER_PATH . '/js/cropperjs/dist/cropper.min.css'
            )
        );

        Requirements::combine_files(
            'cropperfield-all.js',
            array(
                SALTEDCROPPER_PATH . '/js/cropperjs/dist/cropper.min.js',
                SALTEDCROPPER_PATH . '/js/salted-cropper.js'
            )
        );

        return $field;
    }

    public function setCropperRatio($ratio) {
        $this->Ratio = $ratio;
        return $this;
    }

    public function getFileEditFields(File $file) {
        $fields = parent::getFileEditFields($file);
        if ($file->ClassName == 'Image') {
            $name = $this->name;
    		$width = $file->Width;
    		$height = $file->Height;
    		$ratio = $width > 666 ? (666 / $width) : 1;
    		$calc_width = $width * $ratio;
    		$calc_height = $height * $ratio;
    		$styles = " style=\"width:{$calc_width}px; height:{$calc_height}px\"";

            $html = '<div class="salted-cropper"'. $styles .' data-name="'.$name.'" data-cropper-ratio="'.$this->Ratio.'" data-min-width="'. $calc_width .'" data-min-height="' . $calc_height . '"><img src="'.$file->URL.'" width="'.$width.'" height="'.$height.'" /></div>';

            $fields->push(LiteralField::create('SaltedCropper', $html));
            $fields->push(HiddenField::create('CropperRatio')->setValue($this->Ratio));
            $fields->push(HiddenField::create('ContainerX'));
            $fields->push(HiddenField::create('ContainerX'));
    		$fields->push(HiddenField::create('ContainerY'));
    		$fields->push(HiddenField::create('ContainerWidth'));
    		$fields->push(HiddenField::create('ContainerHeight'));
    		$fields->push(HiddenField::create('CropperX'));
    		$fields->push(HiddenField::create('CropperY'));
    		$fields->push(HiddenField::create('CropperWidth'));
    		$fields->push(HiddenField::create('CropperHeight'));
        }

        return $fields;
    }
}
