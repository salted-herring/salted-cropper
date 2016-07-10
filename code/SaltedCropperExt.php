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
			
			//Debugger::inspect('here', false);
			$request = Controller::curr()->request;
			if ($bindings = $request->postVar('cropper_bindings')) {
				
				//Debugger::inspect($request->postVars());
				
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
		if (extension_loaded('imagick')) {
			
			$this->scaleCropImagick($cropped->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
			
		} else {
			$this->scaleCropGD();
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
		//$imagick->resizeImage($canvas_w, $canvas_h, Imagick::FILTER_LANCZOS, 1);
		$imagick->cropImage ( $cropper_w , $cropper_h , $x , $y );
		$imagick->writeImage($image_path);
		$imagick->destroy();
	}
	
	private function scaleCropGD($image_path, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h) {
		
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