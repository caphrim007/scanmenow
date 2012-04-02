<?php

/**
* @author Tim Rupp
*/
class Start_IndexController extends Zend_Controller_Action {
	public $session;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);

		if ($config->misc->firstboot == 1) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('index', 'index', 'setup');
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
		$log = App_Log::getInstance(self::IDENT);
		$this->_redirector = $this->_helper->getHelper('Redirector');

		if (!$this->session->isFirstBoot()) {
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}

		$this->session->setFirstBoot('off');
		$this->_redirector->gotoSimple('index', 'index', 'default');
	}
}

?>
