<?php

/**
* @author Tim Rupp <caphrim007@gmail.com>
*/
class App_Rest_Client extends Zend_Rest_Client {
	protected $_originalUri;

	public function __construct($uri = null) {
		$this->_originalUri = null;

		if (!empty($uri)) {
			$this->_originalUri = $uri;
		}
	}

	public function __set($key, $val) {
		$this->_data[$key] = $val;
	}

	public function getOriginalUri() {
		return $this->_originalUri;
	}

	/**
	* Returns a proxy object for more convenient method calls
	*
	* @param string $namespace  Namespace to proxy or empty string for none
	* @return Zend_Rest_Client_ServerProxy
	*/
	public function getProxy($namespace = '') {
		if (empty($this->_proxyCache[$namespace])) {
			$proxy = new App_Rest_Client_ServerProxy($this, $namespace);
			$this->_proxyCache[$namespace] = $proxy;
		}

		return $this->_proxyCache[$namespace];
	}

	public function __call($method, $args) {
		$methods = array('post', 'get', 'delete', 'put');

		if (in_array(strtolower($method), $methods)) {
			if (!isset($args[0])) {
				$args[0] = $this->_uri->getPath();
			}
			$data = array_slice($args, 1) + $this->_data;
			$response = $this->{'rest' . $method}($args[0], $data);

			//Initializes for next Rest method.
			$this->_data = array();

			$result = new App_Rest_Client_Result($response->getBody());
			$this->setUri($this->_originalUri);
			return $result;
		} else {
			throw new Zend_Rest_Client_Exception('Unknown HTTP method');
		}
	}
}

?>
