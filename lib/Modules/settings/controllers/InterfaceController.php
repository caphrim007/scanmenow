<?php

/**
* @author Tim Rupp
*/
class Settings_InterfaceController extends Zend_Controller_Action {
	public $session;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();
		$now = new Zend_Date;

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);
		$request = $this->getRequest();

		if ($this->session->isFirstBoot()) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('index', 'index', 'start');
		}

		if (!$this->session->acl->isAllowed('Capability', array('settings_interface', 'admin_operator'))) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('permission-denied', 'error', 'default');
		}

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'now' => $now,
			'session' => $this->session
		));
	}

	public function indexAction() {
		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$this->view->assign(array(
			'account' => $this->session
		));
	}

	public function saveAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$limits = $request->getParams();

		try {
			$this->session->settings->askNavigateAway = $limits['askNavigateAway'];
			$this->session->settings->defaultMoreUrl = $limits['defaultMoreUrl'];
			$this->session->settings->limitAccounts = $limits['limitAccounts'];
			$this->session->settings->limitMappings = $limits['limitMappings'];
			$this->session->settings->limitRoles = $limits['limitRoles'];
			$this->session->settings->limitTags = $limits['limitTags'];
			$this->session->settings->limitUrls = $limits['limitUrls'];

			$status = $this->session->settings->update();
		} catch (Exception $error) {
			$log->err($error->getMessage());
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
