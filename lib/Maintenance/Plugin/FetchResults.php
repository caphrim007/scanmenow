<?php

/**
* @author Tim Rupp
*/
class Maintenance_Plugin_FetchResults extends Maintenance_Plugin_Abstract {
	const IDENT = __CLASS__;

	/**
	* @throws Maintenance_Plugin_Exception
	*/
	public function dispatch(Maintenance_Request_Abstract $request) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('scans')
			->joinLeft('scan_credentials', 'scans.scanner_id = scan_credentials.scanner_id', array('username', 'password'))
			->where('status = ?', 'P');

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$results = $stmt->fetchAll();

			foreach ($results as $result) {
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

				$report = $this->_downloadReport($client, $result['scan_uuid']);
				if ($report === false) {
					// Indicates an error or the scan was not yet finished
					continue;
				}

				/**
				* I am going to store the XML report in the database
				* so that I can run post processing on the results and
				* create some metrics and other stuff since the results
				* are available for up to 24 hours (by default).
				*/
				$data = array(
					'status' => 'F',
					'result' => $report
				);

				$where[] = $db->quoteInto('host = ?', $result['host']);
				$where[] = $db->quoteInto('scan_uuid = ?', $result['scan_uuid']);

				$result = $db->update('scans', $data, $where);
			}
		} catch (Exception $error) {
			$log->err($error->getMessage());
			throw new Maintenance_Plugin_Exception($error->getMessage());
		}
	}

	/**
	* Downloads a report from Nessus
	*
	* Reports will only be available after the scan has finished
	*/
	protected function _downloadReport($client, $uuid) {
		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/file/report/download');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$client->setParameterGet(array(
				'report' => $uuid,
				'v2' => true
			));

			$response = $client->request('GET');

			/**
			* A valid response looks like this
			*
			*  <NessusClientData_v2>
			*    <Policy>
			*      <policyName>Scan Policy</policyName>
			*      <policyComments></policyComments>
			*      <Preferences>
			*        <ServerPreferences>
			*          <preference>
			*            <name>use_mac_addr</name>
			*            <value>no</value>
			*          </preference>
			*          <preference>
			*            <name>plugin_set</name>
			*            <value>11589;42070;13795;25037;42455;16143</value>
			*            [..]
			*          </preference>
			*          <preference>
			*            <name>TARGET</name>
			*            <value>192.168.0.100</value>
			*          </preference>
			*          [..]
			*          </plugin_output>
			*          <plugin_version>$Revision: 1.37 $</plugin_version>
			*        </ReportItem>
			*      </ReportHost>
			*    </Report>
			*  </NessusClientData_v2>
			*/
			$response = $response->getBody();

			@$xml = simplexml_load_string($response);
			if ($xml === false) {
				/**
				* Getting here would indicate that the scan started, but
				* the results have not yet finished.
				*
				* You receive a response from Nessus that looks like this
				*
				*  <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
				*  <html>
				*    <head>
				*      <title>404 File not found</title>
				*    </head>
				*    <body>
				*      <h1>File not found</h1>
				*      <p>The requested file was not found</p>
				*    </body>
				*  </html>
				*/
				return false;
			}

			return $xml->asXML();
		} catch (Exception $error) {
			return false;
		}
	}
}

?>
