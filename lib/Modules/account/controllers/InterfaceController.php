<?php

/**
* @author Tim Rupp
*/
class Account_InterfaceController extends Zend_Controller_Action {
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

		$this->view->assign(array(
			'action' => $this->_request->getActionName(),
			'config' => $config,
			'controller' => $this->_request->getControllerName(),
			'module' => $this->_request->getModuleName(),
		));
	}

	public function indexAction() {
		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$id = $request->getParam('accountId');
		if (!is_numeric($id)) {
			throw new Exception('The specified ID is invalid');
		}

		$account = new Account($id);
		$modules = $this->_helper->GetModuleList();

		$this->view->assign(array(
			'session' => $this->session,
			'account' => $account,
			'accountId' => $id,
			'modules' => $modules
		));
	}

	public function saveAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$limits = $request->getParams();
		$accountId = $request->getParam('accountId');
		if (!is_numeric($accountId)) {
			throw new Exception('The specified Account ID is invalid');
		}

		$account = new Account($accountId);

		try {
			$account->settings->limitAccounts = $limits['limitAccounts'];
			$account->settings->limitMappings = $limits['limitMappings'];
			$account->settings->limitRoles = $limits['limitRoles'];
			$account->settings->limitTags = $limits['limitTags'];
			$account->settings->limitUrls = $limits['limitUrls'];
			$account->settings->defaultModule = $limits['defaultModule'];

			$account->settings->update();

			$status = true;
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
