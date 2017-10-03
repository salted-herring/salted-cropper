<?php
use SaltedHerring\Debugger;
class SaltedUploader extends UploadField
{
    private $Ratio          =   null;

    public function __construct($name, $title = null, SS_List $items = null)
    {
        parent::__construct($name, $title);
    }

    public function setCropperRatio($ratio)
    {
        $this->Ratio        =   $ratio;
        return $this;
    }

    public function getFileEditActions(File $file)
    {
        $actions            =   parent::getFileEditActions($file);
        $actions->push(FormAction::create('closeCropper', 'Close')->addExtraClass('ss-ui-action-destructive'));
        return $actions;
    }

    public function getFileEditFields(File $file)
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

        $fields = parent::getFileEditFields($file);

        if ($file->ClassName == 'Image') {
            $name           =   $this->name;
            $width          =   $file->Width;
            $height         =   $file->Height;
            $ratio          =   $width > 666 ? (666 / $width) : 1;
            $calc_width     =   $width * $ratio;
            $calc_height    =   $height * $ratio;
            $styles         =   " style=\"width:{$calc_width}px; height:{$calc_height}px\"";

            $html           =   '<div class="salted-cropper"'. $styles .' data-name="'.$name.'" data-cropper-ratio="'.$this->Ratio.'" data-min-width="'. $calc_width .'" data-min-height="' . $calc_height . '"><img src="'.$file->URL.'" width="'.$width.'" height="'.$height.'" /></div>';

            $fields->push(LiteralField::create('SaltedCropper', $html));
            $fields->push(HiddenField::create('CropperRatio')->setValue($this->Ratio));
            $fields->push($containerX = HiddenField::create('ContainerX'));
            $fields->push($containerX = HiddenField::create('ContainerX'));
            $fields->push($containerY = HiddenField::create('ContainerY'));
            $fields->push($containerWidth = HiddenField::create('ContainerWidth'));
            $fields->push($containerHeight = HiddenField::create('ContainerHeight'));
            $fields->push($cropperX = HiddenField::create('CropperX'));
            $fields->push($cropperY = HiddenField::create('CropperY'));
            $fields->push($cropperWidth = HiddenField::create('CropperWidth'));
            $fields->push($cropperHeight = HiddenField::create('CropperHeight'));

            $record         =   $this->getRecord();

            if ($cropped = $file->getManyManyComponents('CroppedImages')->filter(['FieldName' => $this->Name, 'ObjectClass' => $record->ClassName, 'ObjectID' => $record->ID])->first()) {
                $containerX->setValue($cropped->ContainerX);
                $containerX->setValue($cropped->ContainerX);
                $containerY->setValue($cropped->ContainerY);
                $containerWidth->setValue($cropped->ContainerWidth);
                $containerHeight->setValue($cropped->ContainerHeight);
                $cropperX->setValue($cropped->CropperX);
                $cropperY->setValue($cropped->CropperY);
                $cropperWidth->setValue($cropped->CropperWidth);
                $cropperHeight->setValue($cropped->CropperHeight);
            }

            if ($record     =   $this->getRecord()) {
                $fields->push(HiddenField::create('ObjectClass')->setValue($record->ClassName));
                $fields->push(HiddenField::create('ObjectID')->setValue($record->ID));
            }

        }

        return $fields;
    }

    /**
     * @return Form
     */
    public function EditForm()
    {
        Debugger::inspect('wtf');
        $file = $this->getItem();
        if(!$file) return $this->httpError(404);
        if($file instanceof Folder) return $this->httpError(403);
        if(!$file->canEdit()) return $this->httpError(403);

        // Get form components
        $fields = $this->parent->getFileEditFields($file);
        $actions = $this->parent->getFileEditActions($file);
        $validator = $this->parent->getFileEditValidator($file);
        $form = new Form(
            $this,
            __FUNCTION__,
            $fields,
            $actions,
            $validator
        );
        $form->loadDataFrom($file);
        $form->addExtraClass('small');

        return $form;
    }

    /**
     * @param int $itemID
     * @return SaltedUploader_ItemHandler
     */
    public function getItemHandler($itemID) {
        return SaltedUploader_ItemHandler::create($this, $itemID);
    }

}

/**
 * RequestHandler for actions (edit, remove, delete) on a single item (File) of the UploadField
 *
 * @author Zauberfisch
 * @package forms
 * @subpackages fields-files
 */
class SaltedUploader_ItemHandler extends RequestHandler {

    /**
     * @var UploadFIeld
     */
    protected $parent;

    /**
     * @var int FileID
     */
    protected $itemID;

    private static $url_handlers = array(
        '$Action!' => '$Action',
        '' => 'index',
    );

    private static $allowed_actions = array(
        'delete',
        'edit',
        'EditForm',
    );

    /**
     * @param UploadFIeld $parent
     * @param int $item
     */
    public function __construct($parent, $itemID) {
        $this->parent = $parent;
        $this->itemID = $itemID;

        parent::__construct();
    }

    /**
     * @return File
     */
    public function getItem() {
        return DataObject::get_by_id('File', $this->itemID);
    }

    /**
     * @param string $action
     * @return string
     */
    public function Link($action = null) {
        return Controller::join_links($this->parent->Link(), '/item/', $this->itemID, $action);
    }

    /**
     * @return string
     */
    public function DeleteLink() {
        $token = $this->parent->getForm()->getSecurityToken();
        return $token->addToUrl($this->Link('delete'));
    }

    /**
     * @return string
     */
    public function EditLink() {
        return $this->Link('edit');
    }

    /**
     * Action to handle deleting of a single file
     *
     * @param SS_HTTPRequest $request
     * @return SS_HTTPResponse
     */
    public function delete(SS_HTTPRequest $request) {
        // Check form field state
        if($this->parent->isDisabled() || $this->parent->isReadonly()) return $this->httpError(403);

        // Protect against CSRF on destructive action
        $token = $this->parent->getForm()->getSecurityToken();
        if(!$token->checkRequest($request)) return $this->httpError(400);

        // Check item permissions
        $item = $this->getItem();
        if(!$item) return $this->httpError(404);
        if($item instanceof Folder) return $this->httpError(403);
        if(!$item->canDelete()) return $this->httpError(403);

        // Delete the file from the filesystem. The file will be removed
        // from the relation on save
        // @todo Investigate if references to deleted files (if unsaved) is dangerous
        $item->delete();
    }

    /**
     * Action to handle editing of a single file
     *
     * @param SS_HTTPRequest $request
     * @return ViewableData_Customised
     */
    public function edit(SS_HTTPRequest $request) {
        // Check form field state
        if($this->parent->isDisabled() || $this->parent->isReadonly()) return $this->httpError(403);

        // Check item permissions
        $item = $this->getItem();
        if(!$item) return $this->httpError(404);
        if($item instanceof Folder) return $this->httpError(403);
        if(!$item->canEdit()) return $this->httpError(403);

        Requirements::css(FRAMEWORK_DIR . '/css/UploadField.css');

        return $this->customise(array(
            'Form' => $this->EditForm()
        ))->renderWith($this->parent->getTemplateFileEdit());
    }

    /**
     * @return Form
     */
    public function EditForm() {
        $file = $this->getItem();
        if(!$file) return $this->httpError(404);
        if($file instanceof Folder) return $this->httpError(403);
        if(!$file->canEdit()) return $this->httpError(403);

        // Get form components
        $fields = $this->parent->getFileEditFields($file);
        $actions = $this->parent->getFileEditActions($file);
        $validator = $this->parent->getFileEditValidator($file);
        $form = new Form(
            $this,
            __FUNCTION__,
            $fields,
            $actions,
            $validator
        );
        $form->loadDataFrom($file);
        $form->addExtraClass('small');

        return $form;
    }

    /**
     * @param array $data
     * @param Form $form
     * @param SS_HTTPRequest $request
     */
    public function doEdit(array $data, Form $form, SS_HTTPRequest $request)
    {
        ;
        // Check form field state
        if($this->parent->isDisabled() || $this->parent->isReadonly()) return $this->httpError(403);

        // Check item permissions
        $item = $this->getItem();
        if(!$item) return $this->httpError(404);
        if($item instanceof Folder) return $this->httpError(403);
        if(!$item->canEdit()) return $this->httpError(403);
        $form->saveInto($item);
        $item->write();

        $cropped = $item->getManyManyComponents('CroppedImages')->filter(['FieldName' => $this->parent->Name, 'ObjectClass' => $data['ObjectClass'], 'ObjectID' => $data['ObjectID']])->first();

        if (empty($cropped)) {
            $cropped = new SaltedCroppedImage();
            $form->saveInto($cropped);
            $cropped->write();

            $item->CroppedImages()->add($cropped, ['FieldName' => $this->parent->Name, 'ObjectClass' => $data['ObjectClass'], 'ObjectID' => $data['ObjectID']]);
        } else {
            $form->saveInto($cropped);
            $cropped->write();
        }


        $form->sessionMessage(_t('UploadField.Saved', 'Saved'), 'good');

        return $this->edit($request);
    }

}
