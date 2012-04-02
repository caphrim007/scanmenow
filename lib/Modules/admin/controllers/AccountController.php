<?php

/**
* @author Tim Rupp
*/
class Admin_AccountController extends Zend_Controller_Action {
	public $session;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);

		if ($this->session->isFirstBoot()) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('index', 'index', 'start');
		}

		if (!$this->session->acl->isAllowed('Capability', array('admin_operator', 'edit_user'))) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('permission-denied', 'error', 'default');
		}

		$this->view->assign(array(
			'action' => $this->_request->getActionName(),
			'config' => $config,
			'controller' => $this->_request->getControllerName(),
			'module' => $this->_request->getModuleName(),
			'session' => $this->session
		));
	}

	public function indexAction() {
		$this->view->assign(array(
			'page' => 1
		));
	}

	public function deleteAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$this->_request->setParamSources(array('_POST'));

		$accountId = $this->_request->getParam('id');

		try {
			if (!is_numeric($accountId)) {
				throw new Exception('The specified ID is invalid');
			}

			$account = new Account($accountId);
			$status = $account->delete();
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
		$status = false;
		$message = null;
		$results = array();
		$response = array();
		$limit = 15;

		try {
			$log = App_Log::getInstance(self::IDENT);

			$request = $this->getRequest();
			$request->setParamSources(array('_GET'));

			$account = $this->_helper->GetRequestedAccount();

			if (!empty($account->settings->limitAccounts)) {
				$limit = $account->settings->limitAccounts;
			}

			$page = $request->getParam('page');
			$username = $request->getParam('username');

			$bundle = new Bundle_Account();

			$bundle->page($page);
			$bundle->limit($limit);

			if (!empty($username)) {
				$bundle->username($username);
			}

			$results = $bundle->getAccounts();
			$totalResults = $bundle->count(false);
			$totalPages = ceil($totalResults / $limit);

			$this->view->assign(array(
				'account' => $this->session,
				'limit' => $limit,
				'page' => $page,
				'results' => $results
			));
			$message = $this->view->render('account/search-results.phtml');
			$this->view->clearVars();

			$response['totalResults'] = $totalResults;
			$response['totalPages'] = $totalPages;
			$response['currentPage'] = $page;

			$status = true;
		} catch (Exception $error) {
			$status = false;
			$message = $error->getMessage();
			$log->err($message);
		}

		$response['status'] = $status;
		$response['message'] = $message;

		$this->view->response = $response;
	}
}

?>
