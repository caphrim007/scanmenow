<?php

/**
* @author Tim Rupp
*/
class Setup_IndexController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		parent::init();

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
		$writable = array();

		$path = sprintf('%s/tmp', _ABSPATH);
		if (!is_writable($path)) {
			$writable['tmp'] = $path;
		}

		$path = sprintf('%s/etc/local/', _ABSPATH);
		if (!is_writable($path)) {
			$writable['etc_local'] = $path;
		}

		$path = sprintf('%s/var/log', _ABSPATH);
		if (!is_writable($path)) {
			$writable['var_log'] = $path;
		}

		$path = sprintf('%s/var/cache', _ABSPATH);
		if (!is_writable($path)) {
			$writable['var_cache'] = $path;
		}

		$path = sprintf('%s/var/lib', _ABSPATH);
		if (!is_writable($path)) {
			$writable['var_lib'] = $path;
		}

		$this->view->assign(array(
			'writable' => $writable
		));
	}
}

?>
