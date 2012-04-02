<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_NormalizeFsPath extends Zend_Controller_Action_Helper_Abstract {
	public function direct($path) {
		// Path starts with a slash
		if (substr($path, 0, 1) != '/') {
			$path = '/' . $path;
		}

		while (strpos($path, '/../') !== false) {
			$path = str_replace('/../', '/', $path);
		}

		$path = preg_replace('/\/+/', '/', $path);
		$path = trim($path);

		return $path;
	}
}

?>
