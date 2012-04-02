<?php

/**
* Create log entries for APC cache statistics
*
* This plugin specifically creates log entries of the APC
* cache statistics so that metrics can be created in splunk
* that allow visualization of these statistics over time
*
* @author Tim Rupp <caphrim007@gmail.com>
*/
class Maintenance_Plugin_Contrib_ApcCacheInformation extends Maintenance_Plugin_Abstract {
	protected $_client;

	const IDENT = __CLASS__;

	public function dispatch(Maintenance_Request_Abstract $request) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$result = array();

		$log->debug('Notified of dispatch. Performing task');

		$username = $this->_getUsername();
		$password = $this->_getPassword();

		// Get an authorization token
		$rpc = new App_Rest_Client($config->ws->api->scanmenow->rest->uri);

		$client = $rpc->getProxy();
		$this->_client = $client;

		$token = $this->_login($username, $password);
		if ($token === null) {
			return;
		}

		try {
			$response = $this->_metricCacheStats($token);
			if ($response->isError()) {
				throw new Exception($response->message);
			}

			foreach($response->message as $key => $val) {
				$result[] = sprintf('%s=%s ', $key, $val);
			}

			$message = implode(' ', $result);
			$log->debug($message);
		} catch (Exception $error) {
			$log->err($error->getMessage());
		}
	}

	protected function _login($username, $password) {
		$request = $this->_client->authorize->login(array(
			'username' => $username,
			'password' => $password
		));
		$response = $request->post();
		if ($response->isSuccess()) {
			return $response->message;
		} else {
			return null;
		}
	}

	protected function _getUsername() {
		$config = Ini_Config::getInstance();

		if (isset($config->ws->api->scanmenow->maintenance->username)) {
			$username = $config->ws->api->scanmenow->maintenance->username;
		} else {
			$username = $this->defaultUsername;
		}

		return $username;
	}

	protected function _getPassword() {
		$config = Ini_Config::getInstance();

		if (isset($config->ws->api->scanmenow->maintenance->password)) {
			$password = $config->ws->api->scanmenow->maintenance->password;
		} else {
			$password = $this->defaultPassword;
		}

		return $password;
	}

	protected function _metricCacheStats($token) {
		$request = $this->_client->metric->cache->stats(array(
			'token' => $token
		));
		$response = $request->get();
		return $response;
	}
}

?>
