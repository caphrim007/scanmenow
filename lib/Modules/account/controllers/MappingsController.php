<?php

/**
* @author Tim Rupp
*/
class Account_MappingsController extends Zend_Controller_Action {
	public $session;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);
		$request = $this->getRequest();

		if ($this->session->isFirstBoot()) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('index', 'index', 'start');
		}

		if (!$this->session->acl->isAllowed('Capability', array('admin_operator', 'edit_user'))) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('permission-denied', 'error', 'default');
		}

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $this->session
		));
	}

	public function indexAction() {
		$this->_request->setParamSources(array('_GET'));

		$accountId = $this->_request->getParam('accountId');

		if (!is_numeric($accountId)) {
			throw new Exception('The specified account ID is invalid');
		}

		$account = new Account($accountId);
		$mappings = $account->getMappings();

		$this->view->assign(array(
			'id' => $accountId,
			'account' => $account,
			'session' => $this->session
		));
	}

	public function editAction() {
		$log = App_Log::getInstance(self::IDENT);
		$config = Ini_Config::getInstance();

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$accountId = $request->getParam('accountId');
		$account = new Account($accountId);

		$this->view->assign(array(
			'referer' => @$_SERVER['HTTP_REFERER'],
			'account' => $account
		));
	}

	public function createAction() {
		$this->_request->setParamSources(array('_POST'));

		$accountId = $this->_request->getParam('accountId');
		$mapName = $this->_request->getParam('map-name');

		if (!is_numeric($accountId)) {
			throw new Exception('The specified account ID is invalid');
		}

		$account = new Account($accountId);
		$result = $account->createAccountMapping($mapName);

		if ($result === true ) {
			$this->view->response = array(
				'status' => true
			);
		} else {
			$this->view->response = array(
				'status' => false,
				'message' => 'Failed to create the new account mapping'
			);
		}
	}

	public function deleteAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$accountId = $request->getParam('accountId');
		$mapId = $request->getParam('mapId');

		try {
			if (!is_numeric($accountId)) {
				throw new Zend_Controller_Action_Exception('The specified account ID is invalid');
			}

			if (!is_numeric($mapId)) {
				throw new Zend_Controller_Action_Exception('The specified map ID is invalid');
			}

			$account = new Account($accountId);
			$status = $account->deleteAccountMapping($mapId);
		} catch (Exception $error) {
			$status = false;
			$message = $error->getMessage();
			$log->err($message);
		}

		$this->view->response = array(
			'status' => $status,
			'message' => $message
		);
	}

	public function searchAction() {
		$results = array();
		$limit = 15;

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));
		$page = $request->getParam('page');

		$account = $this->_helper->GetRequestedAccount();
		if (!empty($account->settings->limitMappings)) {
			$limit = $account->settings->limitMappings;
		}

		$results = $account->getMappings($page, $limit);

		if (empty($page)) {
			$page = 1;
		}

		$this->view->assign(array(
			'account' => $this->session,
			'limit' => $limit,
			'page' => $page,
			'results' => $results
		));
	}
}

?>
