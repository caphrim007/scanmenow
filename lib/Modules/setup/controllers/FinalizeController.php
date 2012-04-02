<?php

/**
* @author Tim Rupp
*/
class Setup_FinalizeController extends Zend_Controller_Action {
	protected $_configFile;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$this->_configFile = _ABSPATH.'/etc/local/config.conf';

		$config = Ini_Config::getInstance();

		if ($config->misc->firstboot == 0) {
			$redirector = $this->_helper->getHelper('Redirector');
			$redirector->gotoSimple('index', 'index', 'default');
		}

		$this->view->assign(array(
			'action' => $this->_request->getActionName(),
			'config' => $config,
			'controller' => $this->_request->getControllerName(),
			'module' => $this->_request->getModuleName()
		));
	}

	public function indexAction() {
		$log = App_Log::getInstance(self::IDENT);
		$config = Ini_Config::getInstance();

		try {
			if (!is_writable($this->_configFile)) {
				if (!is_writable(_ABSPATH.'/etc/local/')) {
					throw new Zend_Controller_Action_Exception('The location configuration directory is not writable');
				}
			} else if (file_exists($this->_configFile) && !is_writable($this->_configFile)) {
				throw new Zend_Controller_Action_Exception('The local authentication config file exists but is not writable');
			} else if (!is_writable($this->_configFile)) {
				throw new Zend_Controller_Action_Exception('The local authentication config file is not writable');
			}

			$tmp = array();
			$instance = $config->instance;
			unset($config->instance);

			$config->misc->firstboot = 0;

			$tmp[$instance] = $config;
			$tmp['config']['instance'] = $instance;

			$newConfig = new Zend_Config($tmp);

			$writer = new Zend_Config_Writer_Ini(array(
				'config'   => $newConfig,
				'filename' => $this->_configFile
			));

			$writer->write();
			$log->info('The configuration options have been saved');
			$status = true;
		} catch (Exception $error) {
			$status = false;
			$message = $error->getMessage();
			$log->err($message);
		}
	}
}

?>
