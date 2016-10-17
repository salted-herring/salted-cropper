<?php

class SaltedCropperExt extends DataExtension {
	protected static $db = array(
		'SaltedCropperScheduled'	=>	'Boolean'
	);

	public function updateCMSFields( FieldList $fields ) {
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				CheckboxField::create('SaltedCropperScheduled')
			)
		);
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (Controller::curr() && $this->owner->SaltedCropperScheduled) {

			$request = Controller::curr()->request;
			if ($bindings = $request->postVar('cropper_bindings')) {
				foreach($bindings as $binding) {
					$chain = explode(':', $binding);
					$source = $chain[0];
					$target = $chain[1];
					$source = $this->owner->$source();

					$canvas_x = $request->postVar($target . '_salted_container_x');
					$canvas_y = $request->postVar($target . '_salted_container_y');
					$canvas_w = $request->postVar($target . '_salted_container_width');
					$canvas_h = $request->postVar($target . '_salted_container_height');
					$cropper_x = $request->postVar($target . '_salted_cropper_x');
					$cropper_y = $request->postVar($target . '_salted_cropper_y');
					$cropper_w = $request->postVar($target . '_salted_cropper_width');
					$cropper_h = $request->postVar($target . '_salted_cropper_height');

					if ($this->owner->$target()->exists()) {
						$this->owner->$target()->delete();
					}

					$target = $target . 'ID';

					$this->cropImage($source, $target, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
				}

				$this->owner->SaltedCropperScheduled = false;
			}
		}
	}

	private function cropImage($source, $target, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h) {
		$cropped = $this->duplicateImage($source);
		$cropped->write();
		$cropped->updateFilesystem();
		$this->owner->$target = $cropped->ID;
		$source->SaltedCroppedImageID = $cropped->ID;
		$source->write();
		$source->updateFilesystem();
		if (extension_loaded('imagick')) {
			$this->scaleCropImagick($cropped->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
		} else {
			$this->scaleCropGD($cropped->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
		}
	}

	private function scaleCropImagick($image_path, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h) {

		$imagick = new Imagick($image_path);
		$original_width = $imagick->getImageWidth();
		$x = $cropper_x + $canvas_x;
		$y = $cropper_y + $canvas_y;
		if ($original_width != $canvas_w) {
			$ratio = $original_width / $canvas_w;
			$cropper_w = $cropper_w * $ratio;
			$cropper_h = $cropper_h * $ratio;
			$x = $x * $ratio;
			$y = $y * $ratio;
		}
		$imagick->cropImage( $cropper_w , $cropper_h , $x , $y );
		$imagick->writeImage($image_path);
		$imagick->destroy();
	}

	private function scaleCropGD($image_path, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h) {
		$imgInfo = getimagesize($image_path);
		switch ($imgInfo[2]) {
			case 1:
				$image = imagecreatefromgif($image_path);
				break;
			case 2:
				$image = imagecreatefromjpeg($image_path);
				break;
			case 3:
				$image = imagecreatefrompng($image_path);
				break;
			default:
				return false;
		}

		$original_width = ImageSX($image);
		$x = $cropper_x + $canvas_x;
		$y = $cropper_y + $canvas_y;
		if ($original_width != $canvas_w) {
			$ratio = $original_width / $canvas_w;
			$cropper_w = $cropper_w * $ratio;
			$cropper_h = $cropper_h * $ratio;
			$x = $x * $ratio;
			$y = $y * $ratio;
		}

		$newImg = imagecreatetruecolor($cropper_w, $cropper_h);

		imagecopyresampled($newImg, $image, 0, 0, $x, $y, $cropper_w, $cropper_h, $cropper_w, $cropper_h);
		imagejpeg($newImg, $image_path, 100);
		imagedestroy($newImg);
	}


	public static function scaleCrop($img, $w, $h, $ww, $hh, $x = 0, $y = 0, $flattened_dest = null, $level = null) {

		$imgInfo = getimagesize($img);
		$fn = $img;
		switch ($imgInfo[2]) {
			case 1:
				$image = imagecreatefromgif($img);
				break;
			case 2:
				$image = imagecreatefromjpeg($img);
				break;
			case 3:
				$image = imagecreatefrompng($img);
				break;
			default:
				return false;
		}

		$newImg = imagecreatetruecolor($w, $h);
		/* Check if this image is PNG or GIF, then set if Transparent */
		if (($imgInfo[2] == 1) || ($imgInfo[2] == 3)) {
			imagealphablending($newImg, false);
			imagesavealpha($newImg, true);
			$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
			imagefilledrectangle($newImg, 0, 0, $w, $h, $transparent);
		}

		imagecopyresampled($newImg, $image, 0, 0, 0, 0, $w, $h, $imgInfo[0], $imgInfo[1]);

		$newImg_crop = imagecreatetruecolor($ww, $hh);
		$flattened_crop = imagecreatetruecolor($ww, $hh);
		imagecopy($newImg_crop, $newImg, 0, 0, $x, $y, $w, $h);
		imagecopy($flattened_crop, $newImg, 0, 0, $x, $y, $w, $h);

		$eyePatchPath = $_SERVER['DOCUMENT_ROOT']. '/themes/default/images/white_triangle_png8.png';
		$eyePatchInfo = getimagesize($eyePatchPath);
		$eyeWidth = $eyePatchInfo[0];
		$eyeHeight = $eyePatchInfo[1];
		$dst_y = ($hh - $eyeHeight) * 0.5;

		$eyePatch = imagecreatefrompng($eyePatchPath);
		imagealphablending($eyePatch, true);
		imagesavealpha($newImg_crop, false);
		imagecopy($newImg_crop, $eyePatch, 0, $dst_y, 0, 0, $eyeWidth, $eyeHeight);

		if (!is_null($level)) {
			$LevelPatchInfo = getimagesize($level);
			$LevelPatch = imagecreatefrompng($level);

			$newEyes = imagecreatetruecolor(200, 100);
			imagealphablending($newEyes, false);
			imagesavealpha($newEyes, true);
			$transparent = imagecolorallocatealpha($newEyes, 255, 255, 255, 127);
			imagefilledrectangle($newEyes, 0, 0, 200, 100, $transparent);

			imagecopyresampled($newEyes, $LevelPatch, 0, 0, 0, 0, 200, 100, $LevelPatchInfo[0], $LevelPatchInfo[1]);
			imagealphablending($newEyes, true);
			imagesavealpha($flattened_crop, false);
			imagecopy($flattened_crop, $newEyes, 0, $dst_y, 0, 0, 200, 100);

			imagejpeg($flattened_crop, $flattened_dest, 91);
			imagedestroy($flattened_crop);
			imagedestroy($newEyes);
		}


		imagejpeg($newImg_crop, $fn, 91);
		imagedestroy($newImg);
		imagedestroy($newImg_crop);
		imagedestroy($eyePatch);

		return true;

	}


	private function duplicateImage($src_image) {
		$currenFolder = str_replace('assets/', '', $src_image->Parent()->getRelativePath());
		$folder = Folder::find_or_make($currenFolder.'cropper_resamples');
		$dest_image = new Image();
		$newFileName = ltrim($this->getUniqueFileName($folder->getRelativePath(), $src_image->Name, $src_image->ID, $folder),'_');
		$newFileName = strtolower(str_replace('_','-', $newFileName));

		copy($src_image->getFullPath(), $folder->getFullPath().$newFileName);

		$dest_image->setName($newFileName);
		$dest_image->setParentID($folder->ID);
		return $dest_image;
	}

	private function getUniqueFileName($path, $fileName, $fileID, $parentFolder) {
		$pathinfo = pathinfo($fileName);
		$file = File::get()->filter(array(
			'Name' 		=> $fileName,
			'ParentID'	=> $parentFolder->ID
		))->exclude('ID', $fileID);

		$i = 1;

		while ($file->count() != 0) {
			$fileName = sprintf('%s.%d.%s', $pathinfo['filename'], $i++, $pathinfo['extension']);
			$file = File::get()->filter(array(
				'Name' 		=> $fileName,
				'ParentID'	=> $parentFolder->ID
			))->exclude('ID', $fileID);
		}

		return $fileName;
	}
}
