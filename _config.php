<?php

define('SALTEDCROPPER_PATH', basename(dirname(__FILE__)));
LeftAndMain::require_css(SALTEDCROPPER_PATH . '/js/cropperjs/dist/cropper.min.css');
//LeftAndMain::require_javascript(SALTEDCROPPER_PATH . '/js/cropperjs/dist/cropper.min.js');
LeftAndMain::require_javascript(SALTEDCROPPER_PATH . '/js/salted-cropper.js');
