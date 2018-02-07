<?php
use SaltedHerring\Debugger;
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class SaltedCroppableImage extends DataObject
{
    private $doCrop         =   false;
    /**
     * Database fields
     * @var array
     */
    private static $db = [
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
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'Original'      =>  'Image',
        'Cropped'       =>  'Image'
    ];

    public function URL()
    {
        return $this->getURL();
    }

    public function getURL()
    {
        $image          =   $this->Cropped()->exists() ? $this->Cropped() : $this->Original();
        return $image->getURL();
    }

    public function CMSThumbnail() {
        $image          =   $this->Cropped()->exists() ? $this->Cropped() : $this->Original();
        return $image->Pad($image->stat('cms_thumbnail_width'),$image->stat('cms_thumbnail_height'));
    }

    public function getThumbnail()
    {
        return  $this->Cropped()->exists() ?
                $this->Cropped()->FillMax(200, 120) :
                (
                    $this->Original()->exists() ?
                    $this->Original()->FillMax(200, 120) :
                    null
                );
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
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

        $fields = parent::getCMSFields();
        $fields->removeByName([
            'Cropped',
            'ContainerX',
            'ContainerY',
            'ContainerWidth',
            'ContainerHeight',
            'CropperX',
            'CropperY',
            'CropperWidth',
            'CropperHeight'
        ]);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                UploadField::create(
                    'Original',
                    'Website logo'
                )
            ]
        );

        if ($this->Original()->exists()) {

            $name           =   'salted-cropper-' . ($this->exists() ? $this->ID : 'new');
            $width          =   $this->Original()->Width;
            $height         =   $this->Original()->Height;
            $ratio          =   $width > 666 ? (666 / $width) : 1;
            $calc_width     =   $width * $ratio;
            $calc_height    =   $height * $ratio;
            $styles         =   " style=\"width:{$calc_width}px; height:{$calc_height}px\"";

            $html           =   '<div class="salted-cropper"'. $styles .' data-name="'.$name.'" data-min-width="'. $calc_width .'" data-min-height="' . $calc_height . '"><img src="'.$this->Original()->URL.'" width="'.$width.'" height="'.$height.'" /></div>';

            $fields->push(LiteralField::create('SaltedCropper', $html));
        }

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        if (!$this->Original()->exists() && $this->Cropped()->exists()) {
            $this->Cropped()->delete();
        } elseif ($this->Original()->exists()) {
            $changes = $this->getChangedFields();

            if ($this->hasChanged($changes, 'ContainerX') ||
                $this->hasChanged($changes, 'ContainerY') ||
                $this->hasChanged($changes, 'ContainerWidth') ||
                $this->hasChanged($changes, 'ContainerHeight') ||
                $this->hasChanged($changes, 'CropperX') ||
                $this->hasChanged($changes, 'CropperY') ||
                $this->hasChanged($changes, 'CropperWidth') ||
                $this->hasChanged($changes, 'CropperHeight')) {
                    $this->doCrop = true;
            }
        }

        parent::onBeforeWrite();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if (!empty($this->DO_NOT_WRITE_AGAIN) && $this->DO_NOT_WRITE_AGAIN) {
            $this->doCrop = false;
            return null;
        }

        if ($this->doCrop
            && ($this->ContainerX > 0 ||
                $this->ContainerY > 0 ||
                $this->ContainerWidth > 0 ||
                $this->ContainerHeight > 0 ||
                $this->CropperX > 0 ||
                $this->CropperY > 0 ||
                $this->CropperWidth > 0 ||
                $this->CropperHeight > 0
            )) {

            if (!empty($this->CroppedID)) {
                $this->Cropped()->delete();
            }

            $croppedID = $this->cropImage();
            $this->CroppedID = $croppedID;
            $this->DO_NOT_WRITE_AGAIN = true;
            $this->write();
        }
        $this->doCrop = false;
    }

    private function hasChanged($fields, $field_name)
    {
        if (!empty($fields[$field_name]) && ($fields[$field_name]['before'] != $fields[$field_name]['after'])) {
            return true;
        }

        return false;
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        if ($this->Cropped()->exists()) {
            $this->Cropped()->delete();
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

        $cropped = $this->duplicateImage($this->Original());
        $cropped->isCropped =   true;
        $cropped->write();
        //$this->CroppedID = $cropped->ID;
        if (extension_loaded('imagick')) {
            $this->scaleCropImagick($cropped->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
        } else {
            $this->scaleCropGD($cropped->getFullPath(), $canvas_x, $canvas_y, $canvas_w, $canvas_h, $cropper_x, $cropper_y, $cropper_w, $cropper_h);
        }

        return $cropped->ID;
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

    private function duplicateImage($src_image)
    {
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
            $file = File::get()->filter([
                'Name'         => $fileName,
                'ParentID'    => $parentFolder->ID
            ])->exclude('ID', $fileID);
        }

        return $fileName;
    }

    public function getCropped()
    {
        return $this->Cropped()->exists() ? $this->Cropped() : $this->Original();
    }

    public function forTemplate()
    {
        return $this->renderWith('SaltedCroppableImage');
    }
}
