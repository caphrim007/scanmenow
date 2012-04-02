<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetScannerClient extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct($username, $password, $host, $port = 8834) {
		$uri = Zend_Uri::factory('https');

		$uri->setHost($host);
		$uri->setPort($port);
		$uri->setPath('/login');

		$url = $uri->getUri();

		try {
			$client = new Zend_Http_Client($url);
			$client->setConfig(array(
				'timeout' => 5
			));

			$client->setCookieJar();

			$client->setParameterPost(array(
				'login' => $username,
				'password' => $password
			));
			$response = $client->request('POST');

			/**
			* A valid response looks like this
			*
			*  <reply>
			*    <seq></seq>
			*    <status>OK</status>
			*    <contents>
			*      <token>539a180138270103f1ac2928cdda50fef7ae0ccb059441dd</token>
			*      <user>
			*        <name>scanmenow</name>
			*        <admin>FALSE</admin>
			*      </user>
			*    </contents>
			*  </reply>
			*/
			$response = $response->getBody();

			$xml = new SimpleXMLElement($response);
			$result = $xml->xpath('//token');

			if (empty($result)) {
				throw new Exception('Authentication failed when connecting to the Nessus server');
			} else {
				return $client;
			}
		} catch (Exception $error) {
			return null;
		}
	}
}

?>
