<?php

/**
* @author Tim Rupp
*/
abstract class Charts_Abstract extends Zend_Controller_Action {
	protected $_chart;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);
		$request = $this->getRequest();

		$chart = $this->_helper->LoadMetrxConfig(get_class($this), 'charts');
		$this->_chart = $chart;
		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $this->session,
			'chart' => $chart
		));
	}

	abstract function indexAction();
	abstract function chartAction();
}

?>
