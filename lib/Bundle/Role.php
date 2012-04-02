<?php

/**
* @author Tim Rupp
*/
class Bundle_Role {
	protected $_data;

	const IDENT = __CLASS__;

	public function __construct() {
		$this->reset();
	}

	public function role($role) {
		$this->_data['role'] = $role;
	}

	public function reset($part = null) {
		if ($part === null) {
			$this->_data = array(
				'role' => '',
				'page' => 0,
				'limit' => 0
			);
		} else {
			switch($part) {
				case 'role':
					$this->_data['role'] = '';
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

	public function getRoles() {
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
			->from('roles');

		if (!empty($this->_data['role'])) {
			$sql->where('name LIKE ?', '%'.$this->_data['role'].'%');
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
