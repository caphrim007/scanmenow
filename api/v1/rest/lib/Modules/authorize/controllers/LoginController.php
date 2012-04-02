<?php

/**
* @author Tim Rupp
*/
class Authorize_LoginController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function indexAction() {
		$allowed = false;

		$ini = Ini_Authentication::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$username = $request->getParam('username');
		$password = $request->getParam('password');
		$proxy_account = $request->getParam('proxy');

		try {
			if (!$request->isPost()) {
				throw new Exception('This method expects data to be in a POST request');
			}

			$accountId = Account_Util::getId($username);
			if ($accountId == 0) {
				throw new Exception(sprintf('No account could be found for the provided username "%s"', $username));
			} else {
				$account = new Account($accountId);
			}

			$allowed = $this->_helper->CanUseMethod($accountId, '/authorize/login/index');
			if (!$allowed) {
				throw new Exception(sprintf('Account %s is not allowed to call this method', $account->getUsername()));
			}

			$adapter = new App_Auth_Adapter_Multiple($ini, $username, $password);
			$result = $adapter->authenticate();

			$messages = $result->getMessages();
			foreach($messages as $key => $message) {
				if (empty($message)) {
					continue;
				} else {
					$log->debug($message);
				}
			}

			if ($result->isValid()) {
				$log->debug('Successfully authenticated; returning a token');

				$account_id = Account_Util::getId($username);
				$proxy_id = Account_Util::getId($proxy_account);

				if ($account_id == 0) {
					throw new Api_Exception_Token('Account did not resolve to a known ID');
				}

				if ($proxy_id == 0 && !is_null($proxy_account)) {
					/**
					* Proxy IDs cannot be zero since that is the return value of
					* the getId method. If no proxy was specified though, we _expect_
					* zero to be returned, so keep the above condition an AND
					*/
					throw new Api_Exception_Token('Proxy account did not resolve to a known ID');
				}

				$token = Token_Util::read($account_id, $proxy_id);
				if (empty($token)) {
					$token = Token_Util::create($account_id, $proxy_id);
				}

				$status = 'ok';
				$message = $token;
			} else {
				throw new Api_Exception('The username or password you entered was incorrect');
			}
		} catch (Exception $error) {
			$status = 'error';
			$message = $error->getMessage();
		}

		$this->view->status = $status;
		$this->view->message = $message;
	}

}

?>
