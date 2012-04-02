<?php

class App_Rpc {
	private static $instance;

	public static function getInstance($url = null) {
		$signature = md5($url);

		if (empty(self::$instance[$signature])) {
			if (is_null($url)) {
				$config = Ini_Config::getInstance();
				$url = $config->ws->api->nq->uri;
				$signature = md5($url);
			}

			$client = new Zend_XmlRpc_Client($url);

			self::$instance[$signature] = $client;
		}

		return self::$instance[$signature];
	}
}

?>
