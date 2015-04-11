<?php 
//Class to resize image/create thumbnails last Edgar Pino March 5, 2013

class image_thumbnail {
	protected $_original;
	protected $_originalwidth;
	protected $_originalheight;
	protected $_thumbnailwidth;
	protected $_thumbnailheight;
	protected $_maxSize = 120;
	protected $_canProcess = false;
	protected $_imageType;
	protected $_destination;
	protected $_name;
	protected $_suffix = "_thumb";
	protected $_messages = array();
	

	
	
	public function __construct($image){
		if (is_file($image) && is_readable($image)) {
			$details = getimagesize($image);
			
		} else {
			$details = null;
			$this->_messages[] = "Cannot open or read $image";
		}
		
		if(is_array($details)) {
			$this->_original = $image;
			$this->_originalwidth = $details[0];
			$this->_originalheight = $details[1];
			$this->checkType($details['mime']);
		} else {
			$this->_messages[] = "$image is not an image.";
		}
	}//end of __construct
	
	
	
	/*public function test() {
		echo 'FILE: ' . $this->_original . '<br>';
		echo 'Original Width: ' . $this->_originalwidth . '<br>';
		echo 'Original Height: ' . $this->_originalheight . '<br>';
		echo 'Mime Type: ' . $this->_imageType . '<br>';
		echo 'Destination: ' . $this->_destination . '<br>';
		echo 'Max Size: ' . $this->_maxSize . '<br>';
		echo 'Suffix: ' . $this->_suffix . '<br>';
		echo 'Thumb Width: ' . $this->_thumbnailwidth . '<br>';
		echo 'Thumb Height: ' . $this->_thumbnailheight . '<br>';
		echo 'Base Name: ' . $this->_name . '<br>';
		if($this->_messages) {
			print_r($this->messages);
		}
	}//end of test(); */
	
	
	
	public function setDestination($destination) {
		if(is_dir($destination) && is_writable($destination)) {
			$last = substr($destination, -1);
			if(($last == '/') || ($last == '\\')) {
				$this->_destination = $destination;
			} else {
				$this->_destination = $destination . DIRECTORY_SEPARATOR;
			}
		} else {
			$this->_messages[] = "$destination is not a directory and it must have the right permissions.";
		}
	}//end of setDestination();
	
	public function setMaxSize($size) {
		if(is_numeric($size) && $size > 0)  {
			$this->_maxSize =  abs($size);
		} else {
			$this->_messages[] = "$size must be a number greater than 0";
			$this->_canProcess = false;
		} 
	}//end setMaxSize();
	
	public function setSuffix($suffix) {
		if(preg_match('/^\w+$/', $suffix)) {
			if(strpos($suffix, '_') !== 0 ){
				$this->_suffix = '_' . $suffix;
			} else {
				$this->_suffix = $suffix;
			}
		} else {
			$this->_suffix = '';
			
		}
	}
	
	public function create() {
		
		if($this->_canProcess && $this->_originalwidth != 0 ){
		$this->calDimentions($this->_originalwidth, $this->_originalheight);
		$this->getName();	
		$this->createThumbnail();
		} elseif($this->_originalwidth == 0) {
			$this->_messages[] = 'Cannot Determine Size of ' . $this->_original ;
		}
	}
	
	public function getMessages() {
		return $this->_messages;
	}
	
	protected function calDimentions($width, $height){
		if($width <= $this->_maxSize && $height <= $this->_maxSize) {
			$ratio = 1;
		} elseif($width > $height){
			$ratio = $this->_maxSize/$width; 
		} else {
			$ratio = $this->_maxSize/$height;
		}
		
		$this->_thumbnailwidth = round($width * $ratio);
		$this->_thumbnailheight = round($height * $ratio);
	}
	
	protected function getName(){
		$extentions = array('/\.jpg$/i', '/\.jpeg$/i', '/\.png$/i', '/\.gif$/i');
		$this->_name = preg_replace($extentions, '', basename($this->_original));
	}
	
	
	protected function checkType($mime) {
		$mimeTypes = array('image/jpeg', 'image/png', 'image/gif');
		if(in_array($mime, $mimeTypes)) {
			$this->_canProcess = true;
			$this->_imageType = substr($mime, 6);
		}
	}//end of checkType();
	
	protected function createImageResource(){
		if($this->_imageType == 'jpeg'){
			return imagecreatefromjpeg($this->_original);
		} elseif ($this->_imageType == 'png') {
			return imagecreatefrompng($this->_original);
		} elseif ($this->_imageType == 'gif') {
			return imagecreatefromgif($this->_original);
		}
	}
	
	protected function createThumbnail(){
		$resourse = $this->createImageResource();
		$thumb = imagecreatetruecolor($this->_thumbnailwidth, $this->_thumbnailheight);
		imagecopyresampled($thumb, $resourse, 0, 0, 0, 0, $this->_thumbnailwidth, $this->_thumbnailheight, $this->_originalwidth, $this->_originalheight );
		$newname = $this->_name . $this->_suffix;
		if($this->_imageType == 'jpeg'){
			$newname .= '.jpg';
			strtolower($newname);
			$success = imagejpeg($thumb, $this->_destination . $newname, 100);
		} elseif($this->_imageType == 'png') {
			$newname .= '.png';
			strtolower($newname);
			$success = imagepng($thumb, $this->_destination . $newname, 9);
		} elseif($this->_imageType == 'gif') {
			$newname .= '.gif';
			strtolower($newname);
			$success = imagegif($thumb, $this->_destination . $newname, 100);
		}
		if($success) {
			$this->_messages[] = "$newname successfully created.";
		} else {
			$this->_messages[] = "Failed to create thumbnail for " . basename($this->_original);
		}
		imagedestroy($resourse);
		imagedestroy($thumb);
	}
	
	
	
	
	
	
	
	
	
	
	
}// end of class image_thumbnail






?>