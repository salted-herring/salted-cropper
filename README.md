# Salted Cropper
Salted Herring's Cropper Field for SilverStripe - 4 (Yes, SilverStripe 4, you read it right)

## feature/ss4-upgrade
read on :/

### Usage
1. Download it to SilverStripe's root directory

  ```sh
  git clone git@github.com:salted-herring/salted-cropper.git
  ```
  or

  ```sh
  composer require salted-herring/salted-cropper:dev-feature/ss4-upgrade
  ```

2. Sake it

  ```sh
  sake dev/build
  ```

3. flush frontend and backend's cache

4. Sample code:

    ```php
    ...
    use SaltedHerring\Salted\Cropper\SaltedCroppableImage;
    use SaltedHerring\Salted\Cropper\Fields\CroppableImageField;
    ...
    private static $has_one = array(
        'Photo'     =>  SaltedCroppableImage::class
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

5. Add image > upload/select > save > edit > do your cropping > save

6. Output
    ```html
    $Photo
    $Photo.Cropped
    $Photo.Cropped.SetWidth(100)
    ```

## Legacy
Legcy? What legacy?
