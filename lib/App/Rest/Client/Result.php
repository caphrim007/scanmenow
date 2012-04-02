<?php

/**
* @author Tim Rupp <caphrim007@gmail.com>
*/
class App_Rest_Client_Result implements IteratorAggregate {
	protected $_data;

	public function __construct($data) {
		$this->_data = json_decode($data, true);

		if($this->_data === null) {
			$message = "An error occured while parsing the REST response with JSON.";
			throw new Zend_Rest_Client_Result_Exception($message);
		}
	}

	public function __get($name) {
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		} elseif (isset($this->_data)) {
			return $this->_data;
		} else {
			return null;
		}
	}

	/**
	* Isset Overload
	*
	* @param string $name
	* @return boolean
	*/
	public function __isset($name) {
		if (isset($this->_data[$name])) {
			return true;
		}

		return false;
	}

	/**
	* Implement IteratorAggregate::getIterator()
	*
	* @return ArrayIterator
	*/
	public function getIterator() {
		return $this->_data;
	}

	/**
	* Get Request Status
	*
	* @return boolean
	*/
	public function getStatus() {
		if (isset($this->_data['status'])) {
			$status = strtolower($this->_data['status']);
			return $status;
		} else {
			return null;
		}
	}

	public function isError() {
		$status = $this->getStatus();
		if ($status == 'ok') {
			return false;
		} else {
			return true;
		}
	}

	public function isSuccess() {
		$status = $this->getStatus();
		if ($status == 'ok') {
			return true;
		} else {
			return false;
		}
	}

	/**
	* toString overload
	*
	* Be sure to only call this when the result is a single value!
	*
	* @return string
	*/
	public function __toString() {
		return json_encode($this->_data);
	}
}

?>
