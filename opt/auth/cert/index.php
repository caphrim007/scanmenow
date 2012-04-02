<?php

if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(dirname(dirname(__FILE__)))));
}

if (!defined("_MODPATH")) {
	define("_MODPATH", _ABSPATH.'/lib/Modules');
}

if (!defined("IDENT")) {
	define("IDENT", "CertIndex");
}

require _ABSPATH.'/lib/Autoload.php';

$view 	= new Zend_View;
$config = Ini_Config::getInstance();

try {
	$iniCache = Ini_Cache::getInstance();
	if (isset($iniCache->translate->cache_dir)) {
		if (!is_writeable($iniCache->translate->cache_dir)) {
			throw new App_Exception('Unable to write to the cache directory! Check the directory permissions.');
		}

		$cache = Zend_Cache::factory('Core',
			$iniCache->translate->backend,
			$iniCache->frontend->toArray(),
			$iniCache->translate->toArray()
		);

		Zend_Translate::setCache($cache);
	}
} catch (Exception $error) {
	echo $error->getMessage();
	exit;
}

$translate = new Zend_Translate('gettext',
	sprintf(_ABSPATH.'/usr/share/locale/%s/LC_MESSAGES/messages.mo', $config->misc->locale),
	$config->misc->locale
);

Zend_Registry::set('Zend_Translate', $translate);

$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(false);
$front->addModuleDirectory(_MODPATH);
$front->registerPlugin(new App_Controller_Plugin_Authentication());

$view->addHelperPath(_MODPATH.'/default/views/helpers/');
$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

$front->dispatch();

?>
