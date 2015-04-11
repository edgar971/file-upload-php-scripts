<?php 
require_once('upload-files.php');
require_once('thumbnail.php');

class thumbnail_upload extends file_upload {
	
	//generated thumbnail destination
	protected $_thumbDestination;
	//option to delete the original image
	protected $_deleteOriginal;
	//suffix given to the thumbnail image
	protected $_suffix = '-small';
	//max width and height size of the thumbnail
	protected $_maxthumbsize = 120;

	//privated function to process the image
	protected function processFile($fileName, $error, $type, $size, $tmp_name, $overwrite){
		$ok = $this->checkError($fileName, $error);
		if ($ok) {
		$sizeOk = $this->checkFileSize($fileName, $size);
		$typeOk = $this->checkFileType($fileName, $type);
		if ($sizeOk && $typeOk){
		$name = $this->checkFileName($fileName, $overwrite);
		$success = move_uploaded_file($tmp_name, $this->_destination . $name);
		if ($success) {
		$this->_filenames[] = $name;
		if(!$this->_deleteOriginal) {
			$message = $fileName . ' uploaded successfully';
			if($this->_renamed){
				$message .= " and renamed $name";
			} 
			$this->_messages[] = $message;
		  }
		 $this->createThumbnail($this->_destination . $name);
		 if($this->_deleteOriginal) {
			 unlink($this->_destination . $name);
		 } 	
		} else {
			$this->_messages[] = 'Failed to upload ' . $fileName;
			}
		  }
		
		}		
		
	}
	
	public function __construct($path, $deleteOriginal = false) {
		parent::__construct($path);
		$this->_thumbDestination = $path;
		$this->_deleteOriginal = $deleteOriginal;
	}
	
	public function setThumbDestination($directory){
		if(!is_dir($directory) || !is_writable($directory)) {
			throw new Exception("$path must be a valid and writable directory.");
		}
		$this->_thumbDestination = $directory;
	
	}
	
	public function setThumbSuffix($suffix) {
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
	
	public function setMaxSizeThumb($size) {
		if(is_numeric($size) && $size > 0) {
			$this->_maxthumbsize = abs($size);
		} else {
			$this->_messages[] = "$size must be a number greater than 0";
		}
	}
	
	
	
	
	
	protected function createThumbnail($image) {
		$thumb = new image_thumbnail($image);
		$thumb->setMaxSize($this->_maxthumbsize);
		$thumb->setDestination($this->_thumbDestination);
		$thumb->setSuffix($this->_suffix);
		$thumb->create();
		$messages = $thumb->getMessages();
		$this->_messages = array_merge($this->_messages, $messages);
	
		
		
	}




	
} 


?>