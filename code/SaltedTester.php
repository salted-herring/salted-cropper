<?php use SaltedHerring\Debugger as Debugger;

class SaltedTest extends Page {

    protected static $has_one = array(
        'Image'     =>  'Image'
    );

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', SaltedUploader::create('Image', 'Image'));
        return $fields;
    }
}

class SaltedTest_Controller extends Page_Controller {

}
