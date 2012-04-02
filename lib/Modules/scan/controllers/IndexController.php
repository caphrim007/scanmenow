<?php

/**
* @author Tim Rupp
*/
class Scan_IndexController extends Zend_Controller_Action {
	public $session;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$request = $this->getRequest();

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
		));
	}

	public function indexAction() {
		$status = false;
		$message = null;

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$request = $this->getRequest();

		$request->setParamSources(array('_POST'));

		$scannerId = $request->getParam('scanner');
		$profile = $request->getParam('profile');

		// The remote address is who we are going to scan
		// this is specified to the backend explicitly to
		// prevent people from scanning machines they are
		// not in direct control of
		$host = $_SERVER['REMOTE_ADDR'];

		try {
			if (empty($scannerId)) {
				throw new Exception('You must specify a scanner to use');
			}

			if (empty($profile)) {
				throw new Exception('You must specify a profile to use');
			}

			if (!$this->_helper->ScannerHasProfile($scannerId, $profile)) {
				throw new Exception('The specified scanner does not have the specified profile ID');
			}

			/**
			* Ensures that an account exists on the scanner for this host
			*/
			$this->_prepScanner($scannerId, $host);

			/**
			* And schedule the whole kit-n-kaboodle
			*/
			$result = $this->_scheduleScan($scannerId, $host, $profile);
			if ($result === true) {
				$status = true;
				$message = 'Your scan was started. The scan results will be available shortly';
			}
		} catch (Exception $error) {
			$status = false;
			$message = $error->getMessage();
			$log->err($message);
		}

		$this->view->response = array(
			'status' => $status,
			'message' => $message
		);
	}

	protected function _prepScanner($scannerId, $host) {
		$client = $this->_helper->GetAdminClient($scannerId);
		if ($client === null) {
			throw new Exception('Failed to get a valid HTTP Client to talk to Nessus');
		}

		if ($this->_helper->HasScannerAccount($client, $host)) {
			return true;
		} else {
			$this->_helper->CreateScannerAccount($client, $scannerId, $host);
		}
	}

	protected function _scheduleScan($scannerId, $host, $profile) {
		$date = new Zend_Date();

		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		/**
		* An adhoc client must be used to schedule the scan because
		* in Tenable's infinite wisdom, they don't allow admin users
		* to see all of the scan results of every user.
		*
		* So much for being an admin...
		*/
		$client = $this->_helper->GetAdhocClient($scannerId, $host);
		if ($client === null) {
			throw new Exception('Failed to get a valid HTTP Client to talk to Nessus');
		}

		/**
		* The policy ID is different from the profile ID.
		*
		* Again, Nessus wins hard here. They have numeric policy IDs
		* but you cannot schedule a scan based on a policy name.
		*
		* So this rough translation here gets us a valid policy ID
		* based off of a profile ID which has a unique name.
		*/
		$policyId = $this->_helper->GetPolicyId($client, $profile);

		$uuid = $this->_helper->ScheduleScan($client, $host, $policyId);
		if ($uuid === null) {
			throw new Exception('Failed to schedule the scan.');
		}

		$data = array(
			'date_created' => $date->get(Zend_Date::W3C),
			'host' => $host,
			'scanner_id' => $scannerId,
			'scan_uuid' => $uuid,
		);

		$result = $db->insert('scans', $data);
		if ($result > 0) {
			return true;
		} else {
			throw new Exception('An error occurred while trying to start the scan');
		}
	}
}

?>
