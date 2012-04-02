<?php

/**
* @author Tim Rupp
*/
class App_Controller_Plugin_RestLogin extends Zend_Controller_Plugin_Abstract {
	const IDENT = __CLASS__;

	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		$config = Ini_Config::getInstance();

		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();

		if ($module == 'authorize' && $controller == 'login') {
			return;
		} else if ($module == 'default' && $controller == 'error') {
			return;
		} else if ($module == 'test') {
			return;
		} else {
			$request = $this->getRequest();
			$request->setParamSources(array('_GET', '_POST'));
			$token = $request->getParam('token');

			try {
				if (empty($token)) {
					throw new Exception('The provided authentication token was empty');
				} else {
					return;
				}
			} catch (Exception $e) {
				$request->setModuleName('default');
				$request->setControllerName('error');
				$request->setActionName('error');

				// Set up the error handler
				$error = new Zend_Controller_Plugin_ErrorHandler();
				$error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
				$error->request = clone($request);
				$error->exception = $e;
				$request->setParam('error_handler', $error);
			}
		}
	}
}

?>
