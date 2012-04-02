<?php

/**
* @author Tim Rupp
*/
class Bundle_Account {
	protected $_data;

	const IDENT = __CLASS__;

	public function __construct() {
		$this->reset();
	}

	public function username($account) {
		$this->_data['username'] = $account;
	}

	public function reset($part = null) {
		if ($part === null) {
			$this->_data = array(
				'username' => '',
				'page' => 0,
				'limit' => 0
			);
		} else {
			switch($part) {
				case 'username':
					$this->_data['username'] = '';
					break;
				case 'page':
					$this->_data['page'] = 0;
					break;
				case 'limit':
					$this->_data['limit'] = 0;
					break;
			}
		}
	}

	public function limit($limit) {
		if (is_numeric($limit)) {
			$this->_data['limit'] = $limit;
		}
	}

	public function page($page) {
		if (is_numeric($page)) {
			$this->_data['page'] = $page;
		}
	}

	public function __toString() {
		$sql = $this->_prepareQuery();
		return $sql->__toString();
	}

	public function getAccounts() {
		$log = App_Log::getInstance(self::IDENT);

		$sql = $this->_prepareQuery();
		$log->debug($sql->__toString());

		$stmt = $sql->query();
		$result = $stmt->fetchAll();

		return $result;
	}

	protected function _prepareQuery() {
		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('accounts');

		if (!empty($this->_data['username'])) {
			$sql->where('username LIKE ?', '%'.$this->_data['username'].'%');
		}

		if (!empty($this->_data['limit'])) {
			$sql->limitPage($this->_data['page'], $this->_data['limit']);
		}

		return $sql;
	}

	public function count($limit = true) {
		$sql = $this->_prepareQuery();

		if ($limit === false) {
			$sql->limit(0);
		}

		$stmt = $sql->query();
		$result = $stmt->fetchAll();
		return count($result);
	}
}

?>
