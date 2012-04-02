<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetAdhocClient extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	/**
	* @return Zend_Http_Client
	*/
	public function direct($scannerId, $host) {
		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('scan_credentials')
			->where('username = ?', $host)
			->where('scanner_id = ?', $scannerId);
		$stmt = $sql->query();

		$results = $stmt->fetchAll();
		if (empty($results)) {
			return null;
		}

		$username = $results[0]['username'];
		$password = $results[0]['password'];

		$host = $config->scan->get($scannerId)->host;
		$port = $config->scan->get($scannerId)->port;

		$client = $this->getActionController()
			->getHelper('GetScannerClient')
			->direct($username, $password, $host, $port);

		return $client;
	}
}

?>
