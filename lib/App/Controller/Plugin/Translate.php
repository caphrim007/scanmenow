<?php

/**
* @author Tim Rupp
*/
class App_Controller_Plugin_Translate extends Zend_Controller_Plugin_Abstract {
	const IDENT = __CLASS__;

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$config = Ini_Config::getInstance();

		$poPath = sprintf(_ABSPATH.'/usr/share/locale/%s/LC_MESSAGES/messages.mo', $config->misc->locale);
		if (file_exists($poPath)) {
			$translate = new Zend_Translate('gettext',
				sprintf(_ABSPATH.'/usr/share/locale/%s/LC_MESSAGES/messages.mo', $config->misc->locale),
				$config->misc->locale
			);

			Zend_Registry::set('Zend_Translate', $translate);
		} else {
			throw new Exception('The locale you have specified in your configuration does not point to an existing translation file');
		}
	}
}

?>
