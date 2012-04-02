<?php

/**
* @author Tim Rupp
*/
abstract class Tables_Abstract extends Zend_Controller_Action {
	protected $_table;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);
		$request = $this->getRequest();

		$table = $this->_helper->LoadMetrxConfig(get_class($this), 'tables');
		$this->_table = $table;
		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $this->session,
			'table' => $table
		));
	}

	abstract function indexAction();
	abstract function tableAction();
}

?>
