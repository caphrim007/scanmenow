<?php

/**
* @author Tim Rupp
*/
class Authorize_ValidityController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function indexAction() {
		$allowed = false;

		$ini = Ini_Authentication::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$token = $request->getParam('token');

		try {
			if (!$request->isGet()) {
				throw new Exception('This method expects data to be in a GET request');
			}

			$account = Api_Util::getAccount($token);
			if ($account === null) {
				throw new Api_Exception(sprintf('No account could be mapped to the token %s', $token));
			}

			$allowed = $this->_helper->CanUseMethod($account->id, '/authorize/validity/index');
			if (!$allowed) {
				throw new Api_Exception(sprintf('Account %s is not allowed to call this method', $account->getUsername()));
			}

			$token = new Token($token);

			$status = 'ok';
			$message = $token->getValidity();
		} catch (Exception $error) {
			$status = 'error';
			$message = $error->getMessage();
		}

		$this->view->status = $status;
		$this->view->message = $message;
	}
}

?>
