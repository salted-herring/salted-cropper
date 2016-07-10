# Salted Cropper
Salted Herring's Cropper Field for SilverStripe. This is to avoid the deadly issue that FileSync task will disconnect all cropped images, when using silverstripe-cropperfield

## Usage (internal usage for now)
1. Download it to SilverStripe's root directory

  ```sh
  git clone git@github.com:salted-herring/salted-cropper.git
  ```
  
2. Add use it in code:

  ```php
  private static $extensions = array(
		'SaltedCropperExt'
	);

	private static $has_one = array(
		'Header'		=>	'Image',  //the origin image
		'Cropped'	=>	'Image'   //the cropped
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				UploadField::create('Header'),
				SaltedCropperField::create('Cropped', 'Header', $this, 1/1)
			)
		);
		
		return $fields;
	}
  ```
3. Sake it

  ```sh
  sake dev/build
  ```

4. Use it
