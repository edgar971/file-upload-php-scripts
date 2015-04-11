<?php
//Class to upload files 
class file_upload {
	
	protected $_uploaded = array();
	protected $_destination;
	protected $_max = 41200;
	protected $_messages = array();
	protected $_permitted = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');
	protected $_renamed = false;
	protected $_filenames = array();
	
	public function __construct($path) {
		if(!is_dir($path)  || !is_writable($path)) {
			throw new Exception("$path must be a valid, writable directory.");
		}
		$this->_destination = $path;
		$this->_uploaded = $_FILES;
	}
	public function move($overwrite = false) {
		$field = current($this->_uploaded);
		if(is_array($field['name'])) {
			foreach($field['name'] as $number => $fileName) {
				$this->_renamed = false;
				$this->processFile($fileName, $field['error'][$number], $field['type'][$number], $field['size'][$number], $field['tmp_name'][$number], $overwrite);
			}
		} else { $this->processFile($field['name'], $field['error'], $field['type'], $field['size'], $field['tmp_name'], $overwrite); 
		}
	}
	
	protected function processFile($fileName, $error, $type, $size, $tmp_name, $overwrite){
		$ok = $this->checkError($fileName, $error);
		if ($ok) {
		$sizeOk = $this->checkFileSize($fileName, $size);
		$typeOk = $this->checkFileType($fileName, $type);
		if ($sizeOk && $typeOk){
		$name = $this->checkFileName($fileName, $overwrite);
		$success = move_uploaded_file($tmp_name, $this->_destination . $name);
		if ($success) {
			$message = $fileName . ' uploaded successfully';
			if($this->_renamed){
				$message .= " and renamed $name";
			} 
			$this->_messages[] = $message;
		} else {
			$this->_messages[] = 'Failed to upload ' . $fileName;
			}
		  }
		
		}		
		
	}
	
	public function getMessages() {
		return $this->_messages;
	}
	
	protected function checkError($filename, $error) {
		switch($error) {
			case 0: return true;
			case 1:
			case 2: $this->_messages[] = "$filename exceeds maximum file size: " . $this->getMaxSize(); return true;
			case 3: $this->_messages[] = "Error uploading $filename. Please try again."; return false;
			case 4: $this->_messages[] = "No file selected."; return false;
			default: $this->_messages[] = "A system error occurred while uploading $filename. Please contact your system administrator. "; return false;
			case 5: 
		}
	}
	
	protected function checkFileSize($filename, $size) {
		if($size == 0){
			return false;
		} elseif($size > $this->_max){
			$this->_messages[] = "$filename exceeds the maximum size allowed of " . $this->getMaxSize();
			return false;
		} else {
			return true;
		}
		
		
	}
	
	protected function checkFileType($filename, $type) {
		if(!in_array($type, $this->_permitted)) {
			$this->_messages[] = "$filename is not a permitted file type."; return false;
		} else {
			return true;
		}
	}
	
	public function getMaxSize() {
		return number_format($this->_max/1024, 1) . 'KB';
	}
	
	
	public function setMaxSize($cstmSize) {
		if(!is_numeric($cstmSize)){
			throw new Exception("Maximum Size must be a number");	
		}
		
		$this->_max = (int) $cstmSize;
	}
	
	protected function checkFileName($name, $overwrite){
		$noSpaces = str_replace(' ','-', $name);
		if(basename($noSpaces, 'jpeg')) {
			$noSpaces = str_replace('jpeg', 'jpg',  $noSpaces);
		}
		
		$noSpaces = strtolower($noSpaces);
		if($noSpaces != $name){
			$this->_renamed = true;
		} if(!$overwrite) {
			$existing = scandir($this->_destination);
			if(in_array($noSpaces, $existing)) {
				$dot = strrpos($noSpaces, '.');
				if($dot){
					$base = substr($noSpaces, 0, $dot);
					$extension = substr($noSpaces, $dot);
					
				} else {
					$base = $noSpaces;
					$extension = '';
				}
				  $i = 1;
				  do { $noSpaces = $base . '-' . $i++ . $extension;  
				  } while(in_array($noSpaces, $existing));
				  $this->_renamed = true;
					  
			}
		}
		return $noSpaces;
	}
	
	public function getFileNames() {
		return $this->_filenames;
	}
	
	public function addMimeTypes($types) {
	$types = (array) $types;
	$this->validMimes($types);
	$this->_permitted = $types;
	}
	
	protected function validMimes($types) {
		$alsoValid = array('image/tiff', 'application/pdf', 'text/plain', 'text/rtf');
		$valid = array_merge($this->_permitted, $alsoValid);
		foreach ($types as $type) {
			if(!in_array($type, $valid)) {
				throw new Exception("$type is not a permitted MIME type");
			}
		}
	}
	

	
	
	

}
?>








