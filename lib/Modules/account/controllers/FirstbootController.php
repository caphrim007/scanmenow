<?php

/**
* @author Tim Rupp
*/
class Account_FirstbootController extends Zend_Controller_Action {
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

		$this->view->assign(array(
			'config' => $config,
			'module' => $request->getModuleName(),
			'controller' => $request->getControllerName(),
			'action' => $request->getActionName(),
			'session' => $this->session
		));
	}

	public function toggleAction() {
		$status = false;
		$message = null;

		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$accountId = $request->getParam('id');

		try {
			if (empty($accountId)) {
				throw new Zend_Controller_Action_Exception('The supplied account ID was invalid');
			} else {
				$account = new Account($accountId);
			}

			if ($account->isFirstBoot()) {
				$account->setFirstboot('off');
				$current = 0;
				$message = 'Successfully switched the firstboot flag off';
			} else {
				$account->setFirstboot('on');
				$current = 1;
				$message = 'Successfully switched the firstboot flag on';
			}

			$status = true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			$status = false;
			$message = $error->getMessage();
		}

		$this->view->response = array(
			'status' => $status,
			'message' => $message,
			'current' => $current
		);
	}
}

?>
