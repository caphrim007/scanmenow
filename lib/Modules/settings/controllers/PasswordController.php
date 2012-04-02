<?php

/**
* @author Tim Rupp
*/
class Settings_PasswordController extends Zend_Controller_Action {
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

		if (!$this->session->acl->isAllowed('Capability', array('settings_password', 'admin_operator'))) {
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
		$this->_request->setParamSources(array('_GET'));

		$types = Authentication_Util::authTypes();

		$this->view->assign(array(
			'account' => $this->session,
			'types' => $types
		));
	}

	public function saveAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$newPassword = $request->getParam('newPassword');
		$repeatPassword = $request->getParam('repeatPassword');

		try {
			if ($newPassword != $repeatPassword) {
				throw new Exception('The passwords you typed did not match');
			} else if (empty($newPassword) && empty($repeatPassword)) {
				throw new Exception('The passwords cannot be empty');
			}

			$result = $this->session->setPassword($newPassword);

			if ($result === true) {
				$status = true;
				$log->info('Successfully changed the password');
			} else {
				throw new Exception('Failed to change the password');
			}
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
