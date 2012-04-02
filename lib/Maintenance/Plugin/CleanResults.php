<?php

/**
* @author Tim Rupp
*/
class Maintenance_Plugin_CleanResults extends Maintenance_Plugin_Abstract {
	const IDENT = __CLASS__;

	/**
	* Contains the current "oldest" date to select results prior to
	*
	* @var Zend_Date
	*/
	protected $_date;

	/**
	*
	*
	* This plugin tries to gracefully handle cases where a user may
	* be trying to schedule a scan while this script is trying to
	* clean up old results.
	*
	* It accomplishes this by checking for the 
	*
	* @throws Maintenance_Plugin_Exception
	*/
	public function dispatch(Maintenance_Request_Abstract $request) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$date = new Zend_Date;
		$date = $date->subHour(24);
		$this->_date = $date;

		$totalRemoved = 0;

		try {
			$results = $this->_readScanResults();
			foreach($results as $result) {
				$scannerId = $result['scanner_id'];
				$scanId = $result['scan_uuid'];
				$host = $result['host'];

				while(true) {
					$recent = $this->_hasRecentlyScheduledScans($scannerId, $host);
					if ($recent) {
						sleep(1);
					} else {
						break;
					}
				}

				$client = $this->_getScannerClient($scannerId, $host);
				$result = $this->_deleteResultsFromNessus($client, $scanId);
				if ($result === false) {
					$log->err('Failed to remove the results from Nessus');
					continue;
				}

				$result = $this->_deleteResultsFromDatabase($scanId, $host);
				if ($result === false) {
					$log->err('Failed to remove the results from the database');
					continue;
				} else {
					$totalRemoved = $totalRemoved + 1;
				}
			}

			if ($totalRemoved > 0) {
				$log->debug(sprintf('%s results deleted', $result));
			} else {
				$log->debug('No results needed to be removed');
			}
		} catch (Exception $error) {
			throw new Maintenance_Plugin_Exception($error->getMessage());
		}
	}

	protected function _readScanResults() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('scans')
			->where('date_created <= ?', $this->_date->get(Zend_Date::W3C));
		$stmt = $sql->query();
		$results = $stmt->fetchAll();
		return $results;
	}

	protected function _hasRecentlyScheduledScans($scannerId, $host) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$date = new Zend_Date;
		$date->subMinute(1);

		$sql = $db->select()
			->from('scans')
			->where('scanner_id = ?', $scannerId)
			->where('date_created >= ?', $date->get(Zend_Date::W3C))
			->where('host = ?', $host);
		$stmt = $sql->query();
		$results = $stmt->fetchAll();

		if (count($results) == 0) {
			return false;
		} else {
			return true;
		}
	}

	protected function _getScannerClient($scannerId, $host) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('scans', array())
			->joinLeft('scan_credentials', 'scans.scanner_id = scan_credentials.scanner_id', array('username', 'password', 'scanner_id'))
			->where('scans.scanner_id = ?', $scannerId)
			->where('scans.host = ?', $host);

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$results = $stmt->fetchAll();

			if (count($results) == 0) {
				return false;
			} else {
				$result = $results[0];
			}

			$scannerId = $result['scanner_id'];
			$username = $result['username'];
			$password = $result['password'];

			$host = $config->scan->get($scannerId)->host;
			$port = $config->scan->get($scannerId)->port;

			$helper = new App_Controller_Helper_GetScannerClient();
			$client = $helper->direct($username, $password, $host, $port);
			if ($client === null) {
				throw new Exception('Failed to get a valid HTTP Client to talk to Nessus');
			}

			return $client;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	protected function _deleteResultsFromNessus($client, $scanId) {
		$log = App_Log::getInstance(self::IDENT);

		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/report/delete');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$client->setParameterPost(array(
				'report' => $scanId
			));

			$response = $client->request('POST');

			/**
			* A valid response looks like this
			*
			*  <reply>
			*    <seq>4160</seq>
			*    <status>OK</status>
			*    <contents>
			*      <report>
			*        <name>7f9820c5-5dd8-bde7-8a87-0414859c8a11442b7c9a82eb5bd8</name>
			*      </report>
			*    </contents>
			*  </reply>
			*/
			$response = $response->getBody();

			@$xml = new SimpleXMLElement($response);
			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	protected function _deleteResultsFromDatabase($scanId, $host) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		try {
			$where[] = $db->quoteInto('date_created <= ?', $this->_date->get(Zend_Date::W3C));
			$where[] = $db->quoteInto('scan_uuid = ?', $scanId);
			$where[] = $db->quoteInto('host = ?', $host);

			$result = $db->delete('scans', $where);
			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}
}

?>
