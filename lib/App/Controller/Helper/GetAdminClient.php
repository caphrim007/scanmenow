<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetAdminClient extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	/**
	* @return Zend_Http_Client
	*/
	public function direct($scannerId) {
		$config = Ini_Config::getInstance();

		$username = $config->scan->get($scannerId)->admin->username;
		$password = $config->scan->get($scannerId)->admin->password;
		$host = $config->scan->get($scannerId)->host;
		$port = $config->scan->get($scannerId)->port;

		$client = $this->getActionController()
			->getHelper('GetScannerClient')
			->direct($username, $password, $host, $port);

		return $client;
	}
}

?>
