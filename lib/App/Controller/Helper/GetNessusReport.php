<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetNessusReport extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct(Zend_Http_Client $client, $uuid) {
		$log = App_Log::getInstance(self::IDENT);

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
				return null;
			}

			return $xml->asXML();
		} catch (Exception $error) {
			$log->err($error->getMessage());

			if (!empty($response)) {
				$log->err($response);
			}
			return null;
		}
	}
}

?>
