<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_CreateScannerAccount extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct(Zend_Http_Client $client, $scannerId, $host) {
		$newPassword = md5(mt_rand(0,1000000));

		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/users/add');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$client->setParameterPost(array(
				'login' => $host,
				'password' => $newPassword,
				'admin' => 0
			));

			$response = $client->request('POST');

			/**
			* A valid response looks like this
			*
			*  <reply>
			*    <seq>1111</seq>
			*    <status>OK</status>
			*    <contents>
			*      <user>
			*        <name>zesty</name>
			*        <admin>FALSE</admin>
			*      </user>
			*    </contents>
			*  </reply>
			*/
			$response = $response->getBody();

			$xml = new SimpleXMLElement($response);

			$result = $xml->xpath('//reply[status="OK"]');

			if (empty($result)) {
				return false;
			} else {
				$data = array(
					'scanner_id' => $scannerId,
					'username' => $host,
					'password' => $newPassword
				);
				$db->insert('scan_credentials', $data);

				return true;
			}
		} catch (Exception $error) {
			return null;
		}
	}
}

?>
