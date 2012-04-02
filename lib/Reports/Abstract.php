<?php

/**
* @author Tim Rupp
*/
abstract class Reports_Abstract extends Zend_Controller_Action {
	protected $report;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);
		$request = $this->getRequest();

		$report = $this->_helper->LoadMetrxConfig(get_class($this), 'reports');
		$this->report = $report;
		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $this->session,
			'report' => $report
		));
	}

	abstract function indexAction();

	/**
	* @throws Exception
	* @return boolean
	*/
	protected function saveConfig($config) {
		if (is_array($config)) {
			$config = new Zend_Config($config);
		}

		if (!($config instanceof Zend_Config)) {
			throw new Exception('The provided configuration values must be an instance of Zend_Config');
		} else {
			$report = $this->_helper->SaveMetrxConfig(get_class($this), 'reports', $this->report, $config);
		}

		return $report;
	}

	protected function reloadConfig() {
		$report = $this->_helper->LoadMetrxConfig(get_class($this), 'reports');
		$this->report = $report;
		$this->view->assign(array(
			'report' => $report
		));
	}
}

?>
