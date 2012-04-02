<?php

/**
 * Byte size formatter view helper
 *
 * @author      mkeefe
 * @link        http://www.phpfront.com/php/Convert-Bytes-to-corresponding-size/
 */
class App_View_Helper_ByteSize extends Zend_View_Helper_Abstract {
	public function byteSize($bytes) {
		$size = $bytes / 1024;
		if($size < 1024) {
			$size = number_format($size, 2);
			$size .= ' KB';
		} else {
			if($size / 1024 < 1024) {
				$size = number_format($size / 1024, 2);
				$size .= ' MB';
			} else if ($size / 1024 / 1024 < 1024) {
				$size = number_format($size / 1024 / 1024, 2);
				$size .= ' GB';
			} 
		}

		return $size;
	}
}

?>
