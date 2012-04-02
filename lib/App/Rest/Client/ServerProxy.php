<?php

/**
* The namespace decorator enables object chaining to permit
* calling REST endpoints like "foo/bar/baz"
* as "$remote->foo->bar->baz()".
*
* @author Tim Rupp <caphrim007@gmail.com>
*/
class App_Rest_Client_ServerProxy {
	/**
	* @var App_Rest_Client
	*/
	private $_client = null;

	/**
	* @var string
	*/
	private $_namespace = '';

	/**
	* @var array of App_Rest_Client_ServerProxy
	*/
	private $_cache = array();

	/**
	* Class constructor
	*
	* @param string $namespace
	* @param App_Rest_Client $client
	*/
	public function __construct($client, $namespace = '') {
		$this->_namespace = $namespace;
		$this->_client    = $client;
	}

	public function __get($namespace) {
		$namespace = ltrim("$this->_namespace/$namespace", '/');
		if (!isset($this->_cache[$namespace])) {
			$this->_cache[$namespace] = new $this($this->_client, $namespace);
		}
		return $this->_cache[$namespace];
	}

	public function __call($method, $args) {
		$method = ltrim("$this->_namespace/$method", '/');
		foreach($args[0] as $key => $val) {
			$this->_client->$key = $val;
		}

		$endpoint = sprintf('%s/%s', $this->_client->getOriginalUri(), $method);
		$this->_client->setUri($endpoint);

		return $this->_client;
	}
}

?>
