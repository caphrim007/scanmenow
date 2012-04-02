<?php

/**
* @author Tim Rupp
*/
class App_Controller_Plugin_CheckUnwritable extends Zend_Controller_Plugin_Abstract {
	const IDENT = __CLASS__;

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$config = Ini_Config::getInstance();
		$cache = Ini_Cache::getInstance();

		if ($request->getControllerName() == 'error') {
			return;
		} else {
			$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$systemLogFile = $config->debug->log->messages;
			$systemCacheDir = $cache->translate->cache_dir;

			if (!is_writeable(dirname($systemLogFile)) || (file_exists($systemLogFile) && !is_writable($systemLogFile))) {
				throw new Exception_UnwritableLogDir('The logging directory is not writable');
			}

			if (!is_writeable($systemCacheDir)) {
				throw new Exception_UnwritableCacheDir();
				throw new Exception_UnwritableCacheDir('The translation cache directory is not writable');
			}
		}
	}
}

?>
