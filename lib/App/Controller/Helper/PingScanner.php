<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_PingScanner extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct($host, $port = 8834) {
		$uri = Zend_Uri::factory('https');

		$uri->setHost($host);
		$uri->setPort($port);

		$url = $uri->getUri();

		try {
			$client = new Zend_Http_Client($url);
			$client->setConfig(array(
				'timeout' => 1
			));

			$response = $client->request('GET');
			$response = $response->getBody();

			return 'up';
		} catch (Exception $error) {
			return 'down';
		}
	}
}

?>
