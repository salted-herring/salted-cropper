<?php

class SaltedCropperField extends LiteralField {

	public function __construct($name, $source, $object, $cropper_ratio = 1) {

		$source_image = $object->$source();
		$width = $source_image->Width;
		$height = $source_image->Height;
		$ratio = $width > 700 ? (700 / $width) : 1;
		$calc_width = $width * $ratio;
		$calc_height = $height * $ratio;
		$styles = " style=\"width:{$calc_width}px; height:{$calc_height}px\"";
		parent::__construct(
			$name,
			($object->$name()->exists() ? ('<div id="salted-cropped-'. $name .'" style="max-width: 700px;"><img style="max-width: 100%; height: auto;" src="'.$object->$name()->URL.'" width="" height="" /></div>') : '<h3 id="salted-cropped-'. $name .'">- haven\'t cropped -</h3>').
			($source_image->exists() ? '<div class="salted-cropper"'. $styles .' data-source="'.$source.'" data-name="'.$name.'" data-cropper-ratio="'.$cropper_ratio.'" data-min-width="'. $calc_width .'" data-min-height="' . $calc_height . '"><img src="'.$source_image->URL.'" width="'.$width.'" height="'.$height.'" /></div>' : '')
		);

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
	}
}
