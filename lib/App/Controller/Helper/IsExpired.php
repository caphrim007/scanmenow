<?php

/**
* @author Tim Rupp
* @param $expiration Zend_Date The date that you want to check for expiration
* @param $date Zend_Date Optional date that should be considered the
*	expiration date. Any date before this date andtime will be
*	considered expired.
*/
class App_Controller_Helper_IsExpired extends Zend_Controller_Action_Helper_Abstract {
	public function direct($expiration, $date = null) {
		if ($date === null) {
			$date = new Zend_Date;
		}

		if (!($date instanceof Zend_Date)) {
			throw new Exception('Provided expiration comparison date must be an instance of Zend_Date');
		}

		if ($expiration == 'infinity') {
			return false;
		} else {
			$expiration = new Zend_Date($expiration, Zend_Date::ISO_8601);
		}

		if (!($date instanceof Zend_Date)) {
			throw new Exception('Provided expiration date must be an instance of Zend_Date');
		}

		if ($expiration->isEarlier($date)) {
			return true;
		} else {
			return false;
		}
	}
}

?>
