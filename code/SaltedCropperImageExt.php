<?php

class SaltedCropperImageExt extends DataExtension
{
    private static $many_many = [
        'CroppedImages' =>  'SaltedCroppedImage'
    ];

    private static $many_many_extraFields = [
        'CroppedImages' =>  [
                                'FieldName'     =>  'Varchar(128)',
                                'ObjectClass'   =>  'Varchar(128)',
                                'ObjectID'      =>  'Int'
                            ]
    ];

    public function Cropped($fieldname, $class, $id)
    {
        return $this->owner->getManyManyComponents('CroppedImages')->filter(['FieldName' => $fieldname, 'ObjectClass' => $class, 'ObjectID' => $id])->first();
    }
}
