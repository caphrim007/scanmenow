<?php

/**
* @author Tim Rupp
*/
class App_View_Helper_MetaDataContains extends Zend_View_Helper_Abstract {
	public function metaDataContains($needle, $haystack) {
		foreach($needle as $key) {
			if (in_array($key, $haystack)) {
				return true;
			}
		}

		return false;
	}
}

?>
