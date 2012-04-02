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

		if (!$this->session->acl->isAllowed('Capability', 'edit_capability')) {
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
		$results = array();
		$limit = 10;

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$page = $request->getParam('page');
		$type = $request->getParam('type');
		$limit = $request->getParam('limit');

		if (empty($page)) {
			$page = 1;
		}

		if (empty($limit)) {
			$limit = 10;
		}

		$permissions = new Permissions;

		switch ($type) {
			case 'capability':
				$results = $permissions->get('Capability', null, $page, $limit);
				break;
			case 'queue':
				$results = $permissions->get('Queue', null, $page, $limit);
				break;
			case 'tag':
				$results = $permissions->get('Tag', null, $page, $limit);
				break;
		}

		$this->view->assign(array(
			'limit' => $limit,
			'page' => $page,
			'results' => $results,
			'type' => $type
		));
	}
}

?>
