<?php

/**
* @author Tim Rupp
*/
class Scanner_PingController extends Zend_Controller_Action {
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

	public function indexAction() {
		$info = array();

		$config = Ini_Config::getInstance();

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$scannerId = $request->getParam('scannerId');

		$scannerHost = $config->scan->get($scannerId)->host;
		$scannerPort = $config->scan->get($scannerId)->port;

		$scannerStatus = $this->_helper->PingScanner($scannerHost, $scannerPort);
		if ($scannerStatus == 'up') {
			$status = 'ok';
			$message = 'pong';
		} else {
			$status = false;
			$message = 'down';
		}

		$this->view->response = array(
			'status' => $status,
			'message' => $message
		);
	}
}

?>
