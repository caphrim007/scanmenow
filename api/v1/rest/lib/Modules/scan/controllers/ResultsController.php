<?php

/**
* @author Tim Rupp
*/
class Scan_ResultsController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function createAction() {
		$allowed = false;
		$status = false;
		$message = false;

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$token = $request->getParam('token');
		$host = $request->getParam('host');
		$content = $request->getParam('result');

		try {
			if (!$request->isPost()) {
				throw new Exception('This method expects data to be in a POST request');
			}

			$account = Api_Util::getAccount($token);
			if ($account === null) {
				throw new Api_Exception(sprintf('No account could be mapped to the token %s', $token));
			}

			$allowed = $this->_helper->CanUseMethod($account->id, '/scan/results/create');
			if (!$allowed) {
				throw new Api_Exception(sprintf('Account %s is not allowed to call this method', $account->getUsername()));
			}

			if (empty($host)) {
				throw new Exception('You must specify a "host" item in the argument list');
			}

			$date = new Zend_Date;

			$data = array(
				'date_created' => $date->get(Zend_Date::W3C),
				'host' => $host,
				'result' => $content
			);

			$db = App_Db::getInstance($config->database->default);
			$result = $db->insert('scans', $data);
			if ($result == 0) {
				throw new Exception('Failed to insert the scan results into the database');
			}

			$status = 'ok';
			$message = 'ok';
		} catch (Exception $error) {
			$status = 'error';
			$message = $error->getMessage();
		}

		$this->view->status = $status;
		$this->view->message = $message;
	}
}

?>
