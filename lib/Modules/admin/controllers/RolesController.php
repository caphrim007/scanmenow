<?php

/**
* @author Tim Rupp
*/
class Admin_RolesController extends Zend_Controller_Action {
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

		if (!$this->session->acl->isAllowed('Capability', array('admin_operator', 'edit_role'))) {
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
		$this->view->assign(array(
			'page' => 1
		));
	}

	public function saveAction() {
		$status = false;
		$message = null;
		$addPermissions = array();
		$delPermissions = array();
		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$id = $request->getParam('id');

		if ($id == '_new') {
			$id = Role_Util::create();
		}

		$role = new Role($id);

		$roleName = $request->getParam('role-name');
		$roleDescription = $request->getParam('role-description');

		$selectedMethods = $request->getParam('selected-api');
		$selectedCapabilities = $request->getParam('selected-capability');

		if (!is_array($selectedMethods)) {
			$selectedMethods = array();
		}
		if (!is_array($selectedCapabilities)) {
			$selectedCapabilities = array();
		}

		try {
			if (empty($roleName)) {
				throw new Exception('Role name cannot be empty');
			}

			$role->updateName($roleName);
			$role->updateDescription($roleDescription);

			$addPermissions = array_merge($addPermissions, array_diff($selectedMethods, $role->getIds('ApiMethod')));
			$delPermissions = array_merge($delPermissions, array_diff($role->getIds('ApiMethod'), $selectedMethods));

			$addPermissions = array_merge($addPermissions, array_diff($selectedCapabilities, $role->getIds('Capability')));
			$delPermissions = array_merge($delPermissions, array_diff($role->getIds('Capability'), $selectedCapabilities));

			if (!empty($addPermissions)) {
				foreach($addPermissions as $permission) {
					$role->addPermission($permission);
				}
			}

			if (!empty($delPermissions)) {
				foreach($delPermissions as $permission) {
					$role->deletePermission($permission);
				}
			}

			$status = true;
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

	public function editAction() {
		$allCapabilities = array();
		$selectedCapabilities = array();
		$allMethods = array();
		$selectedMethods = array();
		$allQueues = array();
		$selectedQueues = array();
		$isNew = false;
		$info = array();

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$id = $request->getParam('id');

		if ($id == '_new') {
			$isNew = true;
		} else {
			$role = new Role($id);
			$info = $role->getInfo();
			$selectedMethods = $role->get('ApiMethod', 0, 0);
			$selectedCapabilities = $role->get('Capability', 0, 0);
		}

		$permissions = new Permissions;

		$methods = $permissions->get('ApiMethod', null, 0, 0);
		$capabilities = $permissions->get('Capability', null, 0, 0);

		$this->view->assign(array(
			'allCapabilities' => $capabilities,
			'allMethods' => $methods,
			'id' => $id,
			'info' => $info,
			'isNew' => $isNew,
			'selectedCapabilities' => $selectedCapabilities,
			'selectedMethods' => $selectedMethods,
		));
	}

	public function deleteAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));
		$id = $request->getParam('roleId');

		$role = new Role($id);

		try {
			$result = $role->delete();
			$status = true;
		} catch (Role_Exception $error) {
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

		$account = $this->_helper->GetRequestedAccount();
		if (!empty($account->settings->limitRoles)) {
			$limit = $account->settings->limitRoles;
		}

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$page = $request->getParam('page');

		if (empty($page)) {
			$page = 1;
		}

		$results = Role_Util::getRoles($page, $limit);

		$this->view->assign(array(
			'limit' => $limit,
			'page' => $page,
			'results' => $results
		));




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

			if (!empty($account->settings->limitRoles)) {
				$limit = $account->settings->limitRoles;
			}

			$page = $request->getParam('page');
			$role = $request->getParam('role');

			$bundle = new Bundle_Role();

			$bundle->page($page);
			$bundle->limit($limit);

			if (!empty($role)) {
				$bundle->role($role);
			}

			$results = $bundle->getRoles();
			$totalResults = $bundle->count(false);
			$totalPages = ceil($totalResults / $limit);

			$this->view->assign(array(
				'account' => $this->session,
				'limit' => $limit,
				'page' => $page,
				'results' => $results
			));
			$message = $this->view->render('roles/search-results.phtml');
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
