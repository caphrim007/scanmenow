<?php
/**
* @author Tim Rupp
*/
class App_View_Helper_SplunkView extends Zend_View_Helper_Abstract {
	public function splunkView($params) {
		if ($params instanceof Zend_Config) {
			$params = $params->toArray();
		}

		$default = array(
			'uri' => 'https://localhost:443',
			'username' => 'admin',
			'password' => 'changeme',
			'view' => '/app/savory/view'
		);

		$params = array_merge($default, $params);

		$uri = sprintf('%s/account/insecurelogin?username=%s&password=%s&return_to=%s',
			$params['uri'],
			urlencode($params['username']),
			urlencode($params['password']),
			urlencode($params['view'])
		);

		return $uri;
	}
}

?>
