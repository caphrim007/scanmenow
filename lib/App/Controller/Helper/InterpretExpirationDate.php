<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_InterpretExpirationDate extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct($expiresOn) {
		$expirationDate = null;
		$validExpirationOffsets = array('h','d','w','m');

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		if (empty($expiresOn) || $expiresOn == 'infinity') {
			if (empty($expiresOn)) {
				$log->debug('URL expiration was empty. Checking to see if a global expiration value is set');
			} else if ($expiresOn == 'infinity') {
				$log->debug('URL expiration was set to "infinity". Checking to see if a global expiration value is set');
			}

			if (isset($config->url->expiration->should)) {
				$shouldExpire = $config->url->expiration->should;
			} else {
				$shouldExpire = 'noexpire';
			}

			if ($shouldExpire == 'expire') {
				if(isset($config->url->expiration->range)) {
					$range = $config->url->expiration->range;
					if(isset($config->url->expiration->offset)) {
						$offset = strtolower($config->url->expiration->offset);
						if (!in_array($offset, $validExpirationOffsets)) {
							$log->err('The defined URL expiration offset is not valid');
						} else {
							$expirationDate = new Zend_Date;

							switch($offset) {
								case 'h':
									$expirationDate = $expirationDate->addHour($range);
									break;
								case 'd':
									$expirationDate = $expirationDate->addDay($range);
									break;
								case 'w':
									$expirationDate = $expirationDate->addWeek($range);
									break;
								case 'm':
									$expirationDate = $expirationDate->addMonth($range);
									break;
							}
						}
					} else {
						$log->err('No "offset" is configured for URL expiration!');
					}
				} else {
					$log->err('No "range" is configured for URL expiration!');
				}
			}
		} else {
			$log->debug(sprintf('URL expiration "%s" was specified. Will ignore any global URL expiration settings', $expiresOn));
		}

		if ($expirationDate === null) {
			return null;
		} else {
			return $expirationDate->get(Zend_Date::W3C);
		}
	}
}

?>
