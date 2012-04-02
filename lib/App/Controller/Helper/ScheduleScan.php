<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_ScheduleScan extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct(Zend_Http_Client $client, $host, $policyId) {
		$log = App_Log::getInstance(self::IDENT);

		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/scan/new');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$client->setParameterPost(array(
				'target' => $host,
				'policy_id' => $policyId,
				'scan_name' => $host
			));

			$response = $client->request('POST');

			/**
			* A valid response looks like this
			*
			*  <reply>
			*    <seq></seq>
			*    <status>OK</status>
			*    <contents>
			*      <scan>
			*        <uuid>a09e697e-63ee-3f2d-28b0-2eb2ac16cefaa2c6d2b93e9b36cb</uuid>
			*        <owner>scanmenow</owner>
			*        <start_time>1319820682</start_time>
			*        <scan_name>131.225.82.245</scan_name>
			*      </scan>
			*    </contents>
			*  </reply>
			*/
			$response = $response->getBody();
			$log->debug($response);

			$xml = new SimpleXMLElement($response);

			$result = $xml->xpath('//scan');

			if (empty($result)) {
				throw new Exception('The scan details were not found in the response from Nessus');
			} else {
				return (string)$result[0]->uuid;
			}
		} catch (Exception $error) {
			return null;
		}
	}
}

?>
