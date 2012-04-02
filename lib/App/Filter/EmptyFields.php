<?php

/**
* @author Tim Rupp
*/
class App_Filter_EmptyFields implements Zend_Filter_Interface {
	const IDENT = __CLASS__;

	public function filter($fields) {
		$results = array();

		foreach($fields as $key => $val) {
			if ($val == 0) {
				$results[$key] = $val;
			}

			$val = trim($val);

			if (empty($val)) {
				continue;
			} else {
				$results[$key] = $val;
			}
		}

		return $results;
	}
}

?>
