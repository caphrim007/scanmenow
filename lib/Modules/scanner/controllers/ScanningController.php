<?php

/**
* @author Tim Rupp
*/
class Scanner_ScanningController extends Zend_Controller_Action {
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
		$db = App_Db::getInstance($config->database->default);

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$scannerId = $request->getParam('scannerId');
		$host = $_SERVER['REMOTE_ADDR'];

		$scannerHost = $config->scan->get($scannerId)->host;
		$scannerPort = $config->scan->get($scannerId)->port;

		try {
			$scannerStatus = $this->_helper->PingScanner($scannerHost, $scannerPort);
			if ($scannerStatus == 'down') {
				throw new Exception('Scanner is offline');
			}

			$sql = $db->select()
				->from('scans')
				->where('host = ?', $host)
				->where('scanner_id = ?', $scannerId)
				->where('status = ?', 'P');

			$stmt = $sql->query();
			$results = $stmt->fetchAll();

			$status = true;

			if (count($results) > 0) {
				$message = 'yes';
			} else {
				$message = 'no';
			}
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
