<?php

/**
* @author Tim Rupp
*/
class Role {
	const IDENT = __CLASS__;

	protected $roleId;
	protected $roles;

	public function __construct($roleId) {
		if (is_numeric($roleId)) {
			$this->roleId = $roleId;
		}
	}

	/**
	* @throws Role_Exception
	*/
	public function get($type, $page = 1, $limit = 15) {
		if (isset($this->roles[$type])) {
			$role = $this->roles[$type];
		} else {
			$class = 'Role_'.$type;
			$role = new $class($this->roleId);
		}

		if ($role instanceof Role_Abstract) {
			return $role->get($page, $limit);
		} else {
			throw new Role_Exception('The supplied role type is invalid');
		}
	}

	/**
	* @throws Role_Exception
	*/
	public function getIds($type, $page = 1, $limit = 15) {
		if (isset($this->roles[$type])) {
			$role = $this->roles[$type];
		} else {
			$class = 'Role_'.$type;
			$role = new $class($this->roleId);
		}

		if ($role instanceof Role_Abstract) {
			return $role->getIds($page, $limit);
		} else {
			throw new Role_Exception('The supplied role type is invalid');
		}
	}

	public function getInfo() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'select' => 'SELECT * FROM %s WHERE %s = %s'
		);

		if (!is_numeric($this->roleId)) {
			throw new Role_Exception('The supplied Role ID is invalid');
		}

		try {
			$query = sprintf($sql['select'],
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('id'),
				$db->quote($this->roleId)
			);

			$log->debug($query);

			return $db->fetchRow($query);
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}

	public function updateName($name) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		if (!is_numeric($this->roleId)) {
			throw new Role_Exception('The supplied Role ID is invalid');
		}

		try {
			$query = sprintf($sql['update'],
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('name'),
				$db->quote($name),
				$db->quoteIdentifier('id'),
				$db->quote($this->roleId)
			);

			$log->debug($query);

			$db->query($query);

			return true;
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}

	public function updateDescription($description) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		if (!is_numeric($this->roleId)) {
			throw new Role_Exception('The supplied Role ID is invalid');
		}

		try {
			$query = sprintf($sql['update'],
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('description'),
				$db->quote($description),
				$db->quoteIdentifier('id'),
				$db->quote($this->roleId)
			);

			$log->debug($query);

			$db->query($query);

			return true;
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}

	public function addPermission($permission) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'insert' => '	INSERT INTO %s (
						%s, %s
					) VALUES (%s, %s)'
		);

		if (!is_numeric($this->roleId)) {
			throw new Role_Exception('The supplied Role ID is invalid');
		} else if (empty($permission)) {
			throw new Role_Exception('The supplied permission was empty');
		} else if (!is_numeric($permission)) {
			throw new Role_Exception('The supplied permission was not a numeric value');
		}

		try {
			$query = sprintf($sql['insert'],
				$db->quoteIdentifier('roles_permissions'),
				$db->quoteIdentifier('role_id'),
				$db->quoteIdentifier('permission_id'),
				$db->quote($this->roleId),
				$db->quote($permission)
			);

			$log->debug($query);
			$db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}

	public function deletePermission($permission) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'delete' => '	DELETE FROM %s 
					WHERE %s = %s 
					AND %s = %s'
		);

		if (!is_numeric($this->roleId)) {
			throw new Role_Exception('The supplied Role ID is invalid');
		}

		if (empty($permission)) {
			throw new Role_Exception('The supplied permission was empty');
		}

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('roles_permissions'),
				$db->quoteIdentifier('role_id'),
				$db->quote($this->roleId),
				$db->quoteIdentifier('permission_id'),
				$db->quote($permission)
			);

			$log->debug($query);
			$db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}

	/**
	* @throws Role_Exception
	*/
	public function delete() {
		if (!is_numeric($this->roleId)) {
			throw new Role_Exception('The supplied Role ID is invalid');
		}

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'delete' => '	DELETE FROM %s WHERE %s = %s'
		);

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('id'),
				$db->quote($this->roleId)
			);

			$log->debug($query);
			$db->query($query);
			return true;
		} catch (Exception $error){
			throw new Role_Exception($error->getMessage());
		}
	}
}

?>
