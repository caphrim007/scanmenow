<?php

/**
* @author Tim Rupp
*/
class App_View_Helper_PingScanner extends Zend_View_Helper_Abstract {
	const IDENT = __CLASS__;

	public function pingScanner($uri) {
		$log = App_Log::getInstance(self::IDENT);

		$url = sprintf('%s/ping', $uri);

		try {
			$client = new Zend_Http_Client($url);
			$client->setConfig(array(
				'timeout' => 1
			));

			$response = $client->request('GET');
			$response = json_decode($response->getBody());

			if ($response->status == 'ok') {
				return true;
			} else {
				$log->err($response->message);
				return false;
			}
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}
}

?>
