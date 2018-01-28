# Salted Cropper
Salted Herring's Cropper Field for SilverStripe. This is to avoid the deadly issue that FileSync task will disconnect all cropped images, when using silverstripe-cropperfield.
Please remove silverstripe-cropperfield module if you have already installed it, other it will result in funny conflict.

## 2.0.0
Because in 1.x versions, cropping happens directly on the images, which makes them not available for "reuse", 2.0.0 is now to resolve this issue by redesigning the way that the cropper tool works.

Pleaes note: SaltedUploader will be retired soon. Please replace all SaltedUploader occurrences with CroppableImageField!!

### Usage
1. Download it to SilverStripe's root directory

  ```sh
  git clone git@github.com:salted-herring/salted-cropper.git
  ```
  or

  ```sh
  composer require salted-herring/salted-cropper
  ```

2. Sake it

  ```sh
  sake dev/build
  ```

3. flush frontend and backend's cache

4. Sample code:

    ```php
    protected static $has_one = array(
        'Photo'     =>  'SaltedCroppableImage'
    );


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        ...
        // adding a free cropper
        $fields->addFieldToTab(TAB_NAME, CroppableImageField::create('PhotoID', A_TITLE_TO_THE_FILED);

        // adding cropper with ratio
        $fields->addFieldToTab(TAB_NAME, CroppableImageField::create('PhotoID', A_TITLE_TO_THE_FILED)->setCropperRatio(16/9));
        ...
        return $fields;        
    }

    ```

5. Add image > upload/select > save > edit > do your cropping

6. Output
    ```html
    $Photo
    $Photo.Cropped
    $Photo.Cropped.SetWidth(100)
    ```

## 1.x

This doc is for 1.0 and above. If you are using 1.0- versions, do not use this doc.

### Usage (internal usage for now)
1. Download it to SilverStripe's root directory

  ```sh
  git clone git@github.com:salted-herring/salted-cropper.git
  ```
  or

  ```sh
  composer require salted-herring/salted-cropper
  ```

2. Sake it

  ```sh
  sake dev/build
  ```

3. flush frontend and backend's cache

4. Sample code:

    ```php
    protected static $has_one = array(
        'Photo'     =>  'Image'
    );


    public function getCMSFields() {
        $fields = parent::getCMSFields();
        ...
        // adding a free cropper
        $fields->addFieldToTab(TAB_NAME, SaltedUploader::create('Photo', A_TITLE_TO_THE_FILED);

        // adding cropper with ratio
        $fields->addFieldToTab(TAB_NAME, SaltedUploader::create('Photo', A_TITLE_TO_THE_FILED)->setCropperRatio(16/9));
        ...
        return $fields;        
    }

    ```

5. Upload and image, and then click the edit button - in the drop down area, you will see the cropper area. Do the cropping and then save the image
(NOTE: if you don't save the image editing, before you save the page/data object, it will not take effect)

6. Output
    ```html
    $Photo
    $Photo.Cropped
    $Photo.Cropped.SetWidth(100)
    ```
