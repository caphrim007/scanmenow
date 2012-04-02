<?php

/**
* @author Tim Rupp
*/
class App_Filter_WhoisDate implements Zend_Filter_Interface {
	const IDENT = __CLASS__;

	/**
	* Returns a valid timestamp, parsing and verifying the provided timestamp
	*
	* The whois lookup code is really screwed up and can return
	* some really incorrect dates. This method tries to validate
	* the dates and if they are overly broken, just returns a null
	* value.
	*/
	public function filter($ts) {
		if (preg_match('/^(1|2)[0-9]{3}-(0[1-9]|1[0-2])-([0-2][1-9]|3[0-1])$/', $ts)) {
			// Format: 2007-06-21

			$tmp = explode('-', $ts);

			$year = $tmp[0];
			$month = $tmp[1];
			$day = $tmp[2];

			if ($year > 2500) {
				return null;
			}

			$date = new Zend_Date;
			$date = $date->setTimezone('UTC');
			$date = $date->setDate($ts, 'yyyy-MM-dd');
			$date = $date->setTime('00:00:00');

			return $date->get(Zend_Date::W3C);
		} else if (preg_match('/^(1|2)[0-9]{3}-(0[1-9]|1[0-2])-([0-2][1-9]|3[0-1])\s(2[0-3]|[0-1][0-9]):[0-5][0-9]:[0-5][0-9]$/', $ts)) {
			// Format: 2007-06-21 16:30:15

			$tmp = explode('-', $ts);
			$tmp2 = explode(' ', $ts);

			$year = $tmp[0];
			$month = $tmp[1];
			$day = $tmp[2];

			$time = trim($tmp2[1]);

			if ($year > 2500) {
				return null;
			}

			$date = new Zend_Date;
			$date = $date->setTimezone('UTC');
			$date = $date->setDate($ts, 'yyyy-MM-dd');
			$date = $date->setTime($time);

			return $date->get(Zend_Date::W3C);
		} else {
			return null;
		}
	}
}

?>
