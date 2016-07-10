<?php

class SaltedCropper extends DataObject {
	protected static $db = array(
		'x'			=>	'Int',
		'y'			=>	'Int',
		'width'		=>	'Int',
		'height'		=>	'Int'
	);
	
	protected static $has_one = array(
		'Image'		=>	'Image'
	);
	
	private function cutPortrait($source,$level) {
		$cropped = $this->duplicateImage($source);
		$cropped->write();
		$cropped->updateFilesystem();
		$this->PortraitID = $cropped->ID;
		
		$flattened = $this->duplicateImage($source);
		$flattened->write();
		$flattened->updateFilesystem();
		$this->FlattenedID = $flattened->ID;
		
		Utilities::scaleCrop(
			$cropped->getFullPath(),
			$this->AvatarWidth,
			$this->AvatarHeight,
			self::_PORTRAITWIDTH,
			self::_PORTRAITHEIGHT,
			$this->AvatarX,
			$this->AvatarY,
			$flattened->getFullPath(),
			($level->CreatureEyeGIF()->exists() ? $level->CreatureEyeGIF()->getFullPath() : null)
		);
	}
	
	private function duplicateImage($src_image, $dest_folder = null) {
		
		if (empty($dest_folder)) {
			SaltedHerring\Debugger::inspect($src_image);
			
			die;
		}
		
		$folder = Folder::find_or_make($dest_folder);
		$dest_image = new Image();
		$newFileName = $this->getUniqueFileName($folder->getRelativePath(), $src_image->Name, $src_image->ID, $folder);
		
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