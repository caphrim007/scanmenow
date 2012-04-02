<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetPolicyId extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct(Zend_Http_Client $client, $profileId) {
		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/policy/list');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$response = $client->request('GET');

			/**
			* A valid response looks like this
			*
			*  <reply>
			*    <seq>467</seq>
			*    <status>OK</status>
			*    <contents>
			*      <policies>
			*        <policy>
			*          <policyID>8</policyID>
			*          <policyName>Scan Policy</policyName>
			*          <policyOwner>admin</policyOwner>
			*          <visibility>private</visibility>
			*          <policyContents>
			*            <policyComments></policyComments>
			*            <Preferences>
			*              <ServerPreferences>
			*                <preference>
			*                  <name>use_mac_addr</name>
			*                  <value>no</value>
			*                </preference>
			*                [..]
			*                <PluginName>Nessus TCP scanner</PluginName>
			*                <Family>Port scanners</Family>
			*                <Status>enabled</Status>
			*              </PluginItem>
			*            </IndividualPluginSelection>
			*          </policyContents>
			*        </policy>
			*      </policies>
			*    </contents>
			*  </reply>
			*/
			$response = $response->getBody();

			$xml = new SimpleXMLElement($response);

			$search = sprintf("//policies/policy[policyName='%s']", $profileId);
			$result = $xml->xpath($search);

			if (empty($result)) {
				throw new Exception('The supplied profile ID was not found on the Nessus server');
			} else {
				return (string)$result[0]->policyID;
			}
		} catch (Exception $error) {
			return false;
		}
	}
}

?>
