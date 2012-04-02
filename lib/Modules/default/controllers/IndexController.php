<?php

/**
* @author Tim Rupp
*/
class IndexController extends Zend_Controller_Action {
	protected $_session;

	const IDENT = __CLASS__;

	public function init() {
		$session = null;

		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		if (!empty($sessionUser)) {
			$sessionId = Account_Util::getId($sessionUser);
			$session = new Account($sessionId);
		}
		$request = $this->getRequest();

		$this->_session = $session;

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $session
		));
	}

	public function indexAction() {
		$hasPending = array();

		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		$ipAddress = $_SERVER['REMOTE_ADDR'];
		$sql = $db->select()
			->from('scans')
			->where('host = ?', array($ipAddress))
			->order('date_created DESC')
			->limit(3);

		$stmt = $sql->query();
		$results = $stmt->fetchAll();

		foreach($results as $result) {
			if ($result['status'] == 'P') {
				$hasPending[] = $result['scanner_id'];
			}
		}

		$this->view->assign(array(
			'results' => $results,
			'hasPending' => $hasPending
		));
	}
}

?>
