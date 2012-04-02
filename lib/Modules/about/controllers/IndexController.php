<?php

/**
* @author Tim Rupp
*/
class About_IndexController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);

		if ($sessionId == 0) {
			$session = null;
		} else {
			$session = new Account($sessionId);
		}

		$request = $this->getRequest();

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $session
		));
	}

	public function indexAction() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$date = new Zend_Date;

		$this->view->assign(array(
			'today' => $date
		));
	}
}

?>
