<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_HasScannerAccount extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct(Zend_Http_Client $client, $host) {
		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/users/list');
		$url = $uri->getUri();

		$client->setUri($url);
		$client->resetParameters();

		$response = $client->request('GET');

		/**
		* A valid response looks like this
		*
		*  <reply>
		*    <seq>130</seq>
		*    <status>OK</status>
		*    <contents>
		*      <users>
		*        <user>
		*          <name>zesty</name>
		*          <admin>TRUE</admin>
		*          <lastlogin>1259768554</lastlogin>
		*        </user>
		*        <user>
		*          <name>waffle</name>
		*          <admin>FALSE</admin>
		*          <lastlogin>0</lastlogin>
		*        </user>
		*      </users>
		*    </contents>
		*  </reply>
		*/
		$response = $response->getBody();

		$xml = new SimpleXMLElement($response);

		$search = sprintf('//users/user[name="%s"]', $host);
		$result = $xml->xpath($search);

		if (empty($result)) {
			return false;
		} else {
			return true;
		}
	}
}

?>
