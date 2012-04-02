<?php

/**
* @author Tim Rupp
*/
class Metric_CacheController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function statsAction() {
		$status = false;
		$message = null;
		$allowed = false;
		$results = array();

		$config = Ini_Config::getInstance();
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

			$allowed = $this->_helper->CanUseMethod($account->id, '/metric/cache/stats');
			if (!$allowed) {
				throw new Api_Exception(sprintf('Account %s is not allowed to call this method', $account->username));
			}

			$metrx = new Metrx_ApcCache();
			$results = $metrx->read();

			$status = 'ok';
			$message = $results;
		} catch (Exception_NoApcCache $error) {
			$status = 'nocache';
			$message = $error->getMessage();
			$log->info($message);
		} catch (Exception_NoCacheInfo $error) {
			$status = 'noinfo';
			$message = $error->getMessage();
			$log->info($message);
		} catch (Exception $error) {
			$status = 'error';
			$message = $error->getMessage();
			$log->err($message);
		}

		$this->view->status = $status;
		$this->view->message = $message;
	}
}

?>
