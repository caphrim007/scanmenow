<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetModuleList extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct() {
		$results = array();

		$iter = new DirectoryIterator(_MODPATH);
		
		foreach($iter as $item) {
			if ($item->isDot()) {
				continue;
			} else if ($item->isDir()) {
				$results[] = $item->getFilename();
			}
		}

		sort($results);
		return $results;
	}
}

?>
