<?php

/**
* @author Tim Rupp
*/
class Test_PingController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function indexAction() {
		$request = $this->getRequest();

		try {
			if (!$request->isGet()) {
				throw new Exception('This method expects data to be in a GET request');
			}

			$status = 'ok';
			$message = 'pong';
		} catch (Exception $error) {
			$status = 'error';
			$message = $error->getMessage();
		}

		$this->view->status = $status;
		$this->view->message = $message;
	}
}

?>
