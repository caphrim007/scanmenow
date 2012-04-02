<?php

/**
* @author Tim Rupp
*/
class Permissions_CapabilityController extends Zend_Controller_Action {
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

		if (!$this->session->acl->isAllowed('Capability', 'admin_operator')) {
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

			if (!empty($account->settings->limitUrls)) {
				$limit = $account->settings->limitUrls;
			}

			$page = $request->getParam('page');

			$permissions = new Permissions;
			$results = $permissions->get('Capability', null, $page, $limit);
			$totalPermissions = count($permissions->get('Capability', null, 0, 0));
			$totalPages = ceil($totalPermissions / $limit);

			$this->view->assign(array(
				'account' => $this->session,
				'limit' => $limit,
				'page' => $page,
				'results' => $results
			));
			$message = $this->view->render('capability/search-results.phtml');
			$this->view->clearVars();

			$response['totalPermissions'] = $totalPermissions;
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

	function createAction() {
	
	}

	function saveAction() {
		$status = false;
		$message = null;
		$response = array();

		try {
			$log = App_Log::getInstance(self::IDENT);

			$request = $this->getRequest();
			$request->setParamSources(array('_POST'));

			$permission = $request->getParam('permission');

			$permissions = new Permissions;

			if ($permissions->exists('Capability', $permission)) {
				$status = false;
				$message = 'The specified capability already exists';
			} else {
				$result = $permissions->add('Capability', $permission);
				if ($result === true) {
					$status = true;
					$message = 'Successfully added the capability';
				} else {
					$status = false;
					$message = 'Failed to add the capability';
				}
			}
		} catch (Exception $error) {
			$status = false;
			$message = $error->getMessage();
			$log->err($message);
		}

		$response['status'] = $status;
		$response['message'] = $message;

		$this->view->response = $response;
	}

	function deleteAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$permissionId = $request->getParam('permissionId');

		$permissions = new Permissions;

		try {
			$status = $permissions->delete('Capability', $permissionId);
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
}

?>
