<?php

/**
* @author Tim Rupp
*/
class Scanner_ScanController extends Zend_Controller_Action {
	public $session;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$request = $this->getRequest();

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
		));
	}

	public function createAction() {
		$info = array();

		$config = Ini_Config::getInstance();

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$scannerId = $request->getParam('scannerId');

		$scannerUsername = $config->scan->get($scannerId)->username;
		$scannerPassword = $config->scan->get($scannerId)->password;
		$scannerHost = $config->scan->get($scannerId)->host;
		$scannerPort = $config->scan->get($scannerId)->port;

		$token = $this->_helper->GetScannerToken(
			$scannerUsername, $scannerPassword,
			$scannerHost, $scannerPort
		);

		print_r($token);
exit;
		$host = $_SERVER['REMOTE_ADDR'];

		try {
			$client = new Zend_Http_Client($url);
			$client->setConfig(array(
				'timeout' => 1
			));
			$client->setParameterGet('host', $host);

			$response = $client->request('GET');
			$response = json_decode($response->getBody());

			$status = $response->status;
			$message = $response->message;
		} catch (Exception $error) {
			$status = false;
			$message = $error->getMessage();
		}

		$this->view->response = array(
			'status' => $status,
			'message' => $message
		);
	}
}

?>
