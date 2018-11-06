<?php

namespace SaltedHerring\Salted\Cropper;

use SilverStripe\Assets\FileFinder;
use SilverStripe\Control\Director;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\LiteralField;
use Leochenftw\Debugger;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */

class SaltedCroppableImage extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'SaltedCroppableImage';

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
        'Original'          =>  Image::class
    ];

    public function URL()
    {
        return $this->getURL();
    }

    public function getURL()
    {
        $image              =   $this->Cropped() ? $this->Cropped() : $this->Original();
        return $image->getURL();
    }

    public function getThumbnail()
    {
        return  $this->Cropped() ?
                $this->Cropped()->FitMax(200, 200) :
                (
                    $this->Original()->exists() ?
                    $this->Original()->FitMax(200, 200) :
                    null
                );
    }

    public function Cropped()
    {
        return $this->getCropped();
    }

    public function getCropped()
    {
        if (!$this->Original()->exists()) {
            return null;
        }


        if (empty($this->CropperWidth) && empty($this->CropperHeight)) {
            return $this->Original();
        }

        $canvas_x           =   $this->ContainerX;
        $canvas_y           =   $this->ContainerY;
        $canvas_w           =   $this->ContainerWidth;
        $canvas_h           =   $this->ContainerHeight;
        $cropper_x          =   $this->CropperX;
        $cropper_y          =   $this->CropperY;
        $cropper_w          =   $this->CropperWidth;
        $cropper_h          =   $this->CropperHeight;
        $original_width     =   $this->Original()->getWidth();

        $x                  =   $cropper_x + $canvas_x;
        $y                  =   $cropper_y + $canvas_y;

        if ($original_width != $canvas_w) {
            $ratio = $original_width / $canvas_w;

            $cropper_w = $cropper_w * $ratio;
            $cropper_h = $cropper_h * $ratio;
            $x = $x * $ratio;
            $y = $y * $ratio;
        }

        $y                  =   round($y);
        $x                  =   round($x);
        $cropper_w          =   round($cropper_w);
        $cropper_h          =   round($cropper_h);

        return $this->Original()->crop($x, $y, $cropper_w, $cropper_h);
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        Requirements::css('salted-herring/salted-cropper: client/js/cropperjs/dist/cropper.min.css');
        Requirements::javascript('salted-herring/salted-cropper: client/js/cropperjs/dist/cropper.min.js');
        Requirements::javascript('salted-herring/salted-cropper: client/js/salted-cropper.js');

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
                    'Original image'
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

            $html           =   '<div class="salted-cropper"'. $styles .' data-name="'.$name.'" data-min-width="'. $calc_width .'" data-min-height="' . $calc_height . '"><img src="'.$this->Original()->URL.'?timestamp=' . time() . '" width="'.$width.'" height="'.$height.'" /></div>';

            $fields->push(LiteralField::create('SaltedCropper', $html));
        }

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Event handler called after writing to the database.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $this->Original()->publish('Stage', 'Live');
    }

    public function forTemplate()
    {
        return $this->renderWith([SaltedCroppableImage::class]);
    }
}
