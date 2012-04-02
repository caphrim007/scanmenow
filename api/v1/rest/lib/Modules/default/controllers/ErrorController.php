<?php

/**
* @author Tim Rupp
* @see http://www.enrise.com/2011/01/rest-style-context-switching-part-2/
*/
class ErrorController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function errorAction() {
		try {
			$log = App_Log::getInstance(self::IDENT);
		} catch (App_Exception $error) {
			$this->view->status = 'error';
			$this->view->message = $error->getMessage();
			return;
		}

		$errors = $this->_getParam('error_handler');
		$exception = $errors->exception;

		$log->err($exception->getMessage());
	
		$this->view->status = 'error';
		$this->view->message = $exception->getMessage();
	}

	public function expiredXmlHttpAction() {
		$this->view->assign(array(
			'status' => 'expired',
			'message' => 'The token you provided has expired'
		));
	}

	public function noAccountAction() {
		$this->view->assign(array(
			'status' => 'denied',
			'message' => 'No account could be mapped to the token'
		));
	}

	public function permissionDeniedAction() {
		$this->view->assign(array(
			'status' => 'denied',
			'message' => 'You do not have permission to access this resource'
		));
	}

	public function permissionDeniedMethodAction() {
		$this->view->assign(array(
			'status' => 'denied',
			'message' => 'You do not have permission to call this method'
		));
	}
	public function noTokenAction() {
		$this->view->assign(array(
			'status' => 'denied',
			'message' => 'You must provide an authentication token to access this resource'
		));
	}
}

?>
