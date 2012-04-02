<?php

/**
* @author Tim Rupp
*/
class App_Filter_SingleElement implements Zend_Filter_Interface {
	const IDENT = __CLASS__;

	public function filter($list) {
		if (is_array($list)) {
			return array_shift($list);
		} else {
			return $list;
		}
	}
}

?>
