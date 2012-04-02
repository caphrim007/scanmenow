<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_ScannerHasProfile extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct($scannerId, $profileId) {
		$config = Ini_Config::getInstance();

		$scanner = $config->scan->get($scannerId)->toArray();
		if (empty($scanner)) {
			return false;
		}

		foreach($scanner['profile'] as $profile) {
			if ($profile['id'] == $profileId) {
				return true;
			}
		}

		return false;
	}
}

?>
