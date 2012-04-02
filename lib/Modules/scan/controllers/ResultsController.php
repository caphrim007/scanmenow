<?php

/**
* @author Tim Rupp
*/
class Scan_ResultsController extends Zend_Controller_Action {
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
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$id = $request->getParam('id');
		$format = $request->getParam('format');
		$ipAddress = $_SERVER['REMOTE_ADDR'];

		$sql = $db->select()
			->from('scans')
			->joinLeft('scan_credentials', 'scans.scanner_id = scan_credentials.scanner_id', array('username', 'password'))
			->where('username = ?', $ipAddress);

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$results = $stmt->fetchAll();

			if (empty($results)) {
				throw new Exception('Scanner credentials were not found');
			}

			$scannerId = $results[0]['scanner_id'];
			$username = $results[0]['username'];
			$password = $results[0]['password'];

			$host = $config->scan->get($scannerId)->host;
			$port = $config->scan->get($scannerId)->port;

			$client = $this->_helper->GetScannerClient($username, $password, $host, $port);
			if ($client === null) {
				throw new Exception('Failed to get a valid HTTP Client to talk to Nessus');
			}

			switch($format) {
				case 'builtin_detailed_html.xsl':
				case 'builtin_executive_html.xsl':
					$report = $this->_helper->GetFormattedReport($client, $id, $format);
					break;
				case 'nessus':
				default:
					$report = $this->_helper->GetNessusReport($client, $id);
					break;
			}

			$this->view->assign(array(
				'report' => $report,
				'format' => $format
			));
		} catch (Exception $error) {
			$log->err($error->getMessage());
			throw new Exception($error->getMessage());
		}
	}
}

?>
