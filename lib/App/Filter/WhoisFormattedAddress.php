<?php

/**
* @author Tim Rupp
*/
class App_Filter_WhoisFormattedAddress implements Zend_Filter_Interface {
	const IDENT = __CLASS__;

	/**
	* Creates an address string from the numerous address fields
	*
	* The URL document may or may not have contained an arbitrary
	* list of address* fields (where * is a number; potentially unbounded).
	*
	* This method will find all the address fields in the document
	* and concatenate them together into a single address field
	* that is then stored in the database.
	*
	* @return string
	*/
	public function filter($doc) {
		$result = null;

		foreach($doc as $field => $value) {
			if (substr($field, 0, 7) == 'address') {
				$result .= $value."\n";
			}
		}

		if ($result === null) {
			return null;
		} else {
			return trim($result);
		}
	}
}

?>
