<?php

if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(dirname(dirname(__FILE__)))));
}

if (!defined("_APIPATH")) {
	define("_APIPATH", dirname(__FILE__));
}

if (!defined("_MODPATH")) {
	define("_MODPATH", _ABSPATH.'/api/v1/rest/lib/Modules');
}

if (!defined("IDENT")) {
	define("IDENT", "ApiHttpIndex");
}

require _APIPATH.'/lib/Autoload.php';

Zend_Session::start();

$config = Ini_Config::getInstance();
$log = App_Log::getInstance(IDENT);

$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(true);
$front->addModuleDirectory(_MODPATH);

$front->registerPlugin(new App_Controller_Plugin_RestLogin());
$front->registerPlugin(new App_Controller_Plugin_JsonOutput());

Zend_Controller_Action_HelperBroker::addPath(_APIPATH.'/lib/App/Controller/Helper', 'App_Controller_Helper');

header('Content-Type: application/json');

try {
	$front->dispatch();
} catch (Exception $error) {
	$log->err($error->getMessage());

	$response = array(
		'status' => 'error',
		'message' => $error->getMessage()
	);

	echo Zend_Json::encode($response);
}

?>
