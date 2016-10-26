<?php use SaltedHerring\Debugger as Debugger;

class SaltedUploader extends UploadField {
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

    public function setValue($value, $record = null) {
        $field = $this->name;
        $fileID = $record->$field()->ID;

        if ($file = File::get()->byID($fileID)) {
            
            if ($file->ClassName == 'Image') {

            }
        }

        return parent::setValue($value, $record);
    }
}
