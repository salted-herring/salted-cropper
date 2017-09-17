<?php

use SaltedHerring\Debugger;

class SaltedCroppedImage extends Image
{
    private $doCrop         =   false;
    private static $db      =   [
        'ContainerX'        =>  'Int',
        'ContainerY'        =>  'Int',
        'ContainerWidth'    =>  'Int',
        'ContainerHeight'   =>  'Int',
        'CropperX'          =>  'Int',
        'CropperY'          =>  'Int',
        'CropperWidth'      =>  'Int',
        'CropperHeight'     =>  'Int'
    ];

    /**
     * Belongs_many_many relationship
     * @var array
     */
    private static $belongs_many_many = [
        'SourceImage'       =>  'Image'
    ];

    private function hasChanged($fields, $field_name)
    {
        if (!empty($fields[$field_name]) && ($fields[$field_name]['before'] != $fields[$field_name]['after'])) {
            return true;
        }

        return false;
    }
    public function onBeforeWrite()
    {
        $changes = $this->getChangedFields();

        if ($this->hasChanged($changes, 'Filename') ||
            $this->hasChanged($changes, 'ContainerX') ||
            $this->hasChanged($changes, 'ContainerY') ||
            $this->hasChanged($changes, 'ContainerWidth') ||
            $this->hasChanged($changes, 'ContainerHeight') ||
            $this->hasChanged($changes, 'CropperX') ||
            $this->hasChanged($changes, 'CropperY') ||
            $this->hasChanged($changes, 'CropperWidth') ||
            $this->hasChanged($changes, 'CropperHeight')) {
                $this->doCrop = true;
        }

        parent::onBeforeWrite();
        if (empty($this->ID)) {
            $this->ShowInSearch = false;
            $this->duplicateImage();
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->doCrop
            && ($this->ContainerX > 0 ||
                $this->ContainerY > 0 ||
                $this->ContainerWidth > 0 ||
                $this->ContainerHeight > 0 ||
                $this->CropperX > 0 ||
                $this->CropperY > 0 ||
                $this->CropperWidth > 0 ||
                $this->CropperHeight > 0
            )
        ) {
            $this->cropImage();
            $this->doCrop = false;
        }
    }

    private function cropImage()
    {
        $canvas_x = $this->ContainerX;
        $canvas_y = $this->ContainerY;
        $canvas_w = $this->ContainerWidth;
        $canvas_h = $this->ContainerHeight;
        $cropper_x = $this->CropperX;
        $cropper_y = $this->CropperY;
        $cropper_w = $this->CropperWidth;
        $cropper_h = $this->CropperHeight;

        if (extension_loaded('imagick')) {
            $this->scaleCropImagick($this->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
        } else {
            $this->scaleCropGD($this->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
        }
    }

    private function scaleCropImagick($image_path, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h)
    {

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

    private function scaleCropGD($image_path, $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h)
    {
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

        switch ($imgInfo[2]) {
            case 1:
                imagegif($newImg, $image_path);
                break;
            case 2:
                imagejpeg($newImg, $image_path, 100);
                break;
            case 3:
                imagepng($newImg, $image_path);
                break;
            default:
                imagejpeg($newImg, $image_path, 100);
                break;
        }

        imagedestroy($newImg);
    }

    private function duplicateImage()
    {
        $currenFolder = str_replace('assets/', '', $this->Parent()->getRelativePath());

        $folder = Folder::find_or_make($currenFolder.'cropper_resamples');
        $newFileName = ltrim($this->getUniqueFileName($folder->getRelativePath(), $this->Name, $this->ID, $folder),'_');
        $newFileName = strtolower(str_replace('_','-', $newFileName));
        copy($this->getFullPath(), $folder->getFullPath().$newFileName);
        $this->ParentID = $folder->ID;
        $this->Filename = $folder->getRelativePath().$newFileName;
    }

    private function getUniqueFileName($path, $fileName, $fileID, $parentFolder)
    {
        $pathinfo = pathinfo($fileName);
        $file = File::get()->filter(array(
            'Name'         => $fileName,
            'ParentID'    => $parentFolder->ID
        ))->exclude('ID', $fileID);

        $i = 1;

        while ($file->count() != 0) {
            $fileName = sprintf('%s.%d.%s', $pathinfo['filename'], $i++, $pathinfo['extension']);
            $file = File::get()->filter(array(
                'Name'         => $fileName,
                'ParentID'    => $parentFolder->ID
            ))->exclude('ID', $fileID);
        }

        return $fileName;
    }
}
