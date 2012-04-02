<?php

/**
* @author Tim Rupp
*/
class Account {
	const IDENT = __CLASS__;

	public $acl;
	public $role;
	public $settings;
	public $contacts;

	protected $_data;
	protected $_id;
	protected $_roles;

	public function __construct($id) {
		if (!is_numeric($id)) {
			throw new Exception('Account ID must be a number');
		} else {
			$this->_id = $id;
		}

		try {
			$this->_roles = $this->getRoles();

			$this->loadAccountData();
		} catch (Exception $error) {
			throw new Exception($error->getMessage());
		}


		$this->acl = new Account_Acl($id, $this->primary_role);
		$this->role = new Account_Role($id);
		$this->settings = new Account_Settings($id);
	}

	public function __set($key, $val) {
		switch($key) {
			case 'id':
				return false;
			case 'primary_role':
				if (!in_array($val, $this->_roles)) {
					return false;
				}
				break;
		}

		$this->_data[$key] = $val;
	}

	public function __get($key) {
		switch($key) {
			case 'id':
				return $this->_id;
			default:
				break;
		}

		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		} else {
			return false;
		}
	}

	public function loadAccountData() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('accounts')
			->where('id = ?', $this->_id);

		$log->debug($sql->__toString());
		$stmt = $sql->query();
		$result = $stmt->fetchAll();
		if (empty($result)) {
			throw new Exception('The specified account ID was not found');
		} else {
			foreach($result[0] as $key => $val) {
				$this->$key = $val;
			}
		}

		return true;
	}

	public function createAccountMapping($mapping) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$date = new Zend_Date;

		$mapping = trim($mapping);

		$data = array(
			'account_id' => $this->_id,
			'username' => $mapping,
			'date_created' => $date->get(Zend_Date::W3C)
		);

		try {
			$result = $db->insert('accounts_maps', $data);

			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	public function delete() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'delete' => 'DELETE FROM %s WHERE %s = %s'
		);

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}

	public function deleteAccountMapping($mapId) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'delete' => 'DELETE FROM %s WHERE %s = %s'
		);

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('accounts_maps'),
				$db->quoteIdentifier('id'),
				$db->quote($mapId)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}

	}

	public function getMappings($page = 1, $limit = 15) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('accounts_maps', array('id', 'account_id', 'username'))
			->where('account_id = ?', $this->_id);

		if (!empty($limit)) {
			$sql->limitPage($page, $limit);
		}

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			return $stmt->fetchAll();
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}

	public function getId() {
		return $this->_id;
	}

	/**
	* @throws Account_Exception
	*/
	public function getUsername() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'select' => 'SELECT %s FROM %s WHERE %s = %s'
		);

		if (!is_null($this->username)) {
			return $this->username;
		}

		try {
			$query = sprintf($sql['select'],
				$db->quoteIdentifier('username'),
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->fetchOne($query);

			if (empty($result)) {
				$result = null;
			}

			$this->username = $result;
			return $result;
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}

	/**
	* @throws Account_Exception
	*/
	public function setUsername($username) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'select' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		try {
			$query = sprintf($sql['select'],
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('username'),
				$db->quote($username),
				$db->quoteIdentifier('id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->query($query);
			$this->username = $username;
			return true;
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}

	public function getProperName() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'select' => 'SELECT %s FROM %s WHERE %s = %s'
		);

		if (!is_null($this->properName)) {
			return $this->properName;
		}

		try {
			$query = sprintf($sql['select'],
				$db->quoteIdentifier('proper_name'),
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->fetchOne($query);

			$this->properName = $result;
			return $result;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return null;
		}
	}

	/**
	* @throws Account_Exception
	*/
	public function getPrimaryRole() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'select' => 'SELECT %s FROM %s WHERE %s = %s'
		);

		if (!empty($this->primaryRole)) {
			return $this->primaryRole;
		}

		try {
			$query = sprintf($sql['select'],
				$db->quoteIdentifier('primary_role'),
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('id'),
				$db->quote($this->getId())
			);

			$log->debug($query);

			$result = $db->fetchRow($query);
			$this->primaryRole = $result['primary_role'];
			return $result['primary_role'];
		} catch (Exception $error) {
			$log->err($error->getMessage());
			throw new Account_Exception($error->getMessage());
		}
	}

	/**
	* @throws Account_Exception
	*/
	public function setPrimaryRole($primaryRole) {
		$found = false;

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		$roles = $this->getRoles();
		foreach($roles as $role) {
			if ($role['role_id'] == $primaryRole) {
				$found = true;
				break;
			}
		}

		if ($found === false) {
			$log->info('The specified primary role was not found in the list of roles currently assigned to this account');
			return false;
		}

		try {
			$query = sprintf($sql['update'],
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('primary_role'),
				$db->quote($primaryRole),
				$db->quoteIdentifier('id'),
				$db->quote($this->getId())
			);

			$log->debug($query);

			$db->query($query);
			$this->primaryRole = $primaryRole;
			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			throw new Account_Exception($error->getMessage());
		}
	}

	public function getRoles() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'select' => '	SELECT 	%s.%s AS %s,
						%s.%s AS %s,
						%s.%s AS %s
					FROM %s
					LEFT JOIN %s
					ON %s.%s = %s.%s
					WHERE %s.%s = %s'
		);

		try {
			$query = sprintf($sql['select'],
				$db->quoteIdentifier('accounts_roles'),
				$db->quoteIdentifier('id'),
				$db->quoteIdentifier('id'),
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('name'),
				$db->quoteIdentifier('name'),
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('id'),
				$db->quoteIdentifier('role_id'),
				$db->quoteIdentifier('accounts_roles'),
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('accounts_roles'),
				$db->quoteIdentifier('role_id'),
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('id'),
				$db->quoteIdentifier('accounts_roles'),
				$db->quoteIdentifier('account_id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			return $db->fetchAll($query);
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return array();
		}
	}

	public function getEmailContacts() {
		return $this->_getContacts('email');
	}

	public function getImContacts() {
		return $this->_getContacts('im');
	}

	public function getPhoneContacts() {
		return $this->_getContacts('phone');
	}

	public function setPassword($password) {
		$log = App_Log::getInstance(self::IDENT);
		$authTypes = Authentication_Util::authTypes();
		$result = false;

		if (Authentication_Util::hasAuthType('DbTable')) {
			$result = $this->_setDatabasePassword($password);

			if ($result === false) {
				return $result;
			} else {
				$this->password = $password;
			}
		} else {
			$log->err('The database authentication type is not configured; skipping it');
		}

		if (Authentication_Util::hasAuthType('Array')) {
			$result = $this->_setFailsafePassword($password);
		} else {
			$log->err('The failsafe authentication type is not configured; skipping it');
			return true;
		}

		return $result;
	}

	public function setProperName($name) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		try {
			$query = sprintf($sql['update'],
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('proper_name'),
				$db->quote($name),
				$db->quoteIdentifier('id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->query($query);

			$this->properName = $name;
			return true;
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}

	protected function _setDatabasePassword($password) {
		$log = App_Log::getInstance(self::IDENT);
		$auth = Ini_Authentication::getInstance();
		$username = $this->getUsername();
		$hashedPassword = md5($password);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		foreach ($auth->auth as $key => $type) {
			$db = App_Db::getInstance($type->params->adapter);

			if ($type->adapter != 'DbTable') {
				continue;
			}

			try {
				$query = sprintf($sql['update'],
					$db->quoteIdentifier($type->params->tableName),
					$db->quoteIdentifier($type->params->credentialColumn),
					$db->quote($hashedPassword),
					$db->quoteIdentifier($type->params->identityColumn),
					$db->quote($username)
				);

				$log->debug($query);

				$result = $db->query($query);
			} catch (Exception $error) {
				$log->err($error->getMessage());
				return false;
			}
		}

		return true;
	}

	protected function _setFailsafePassword($password) {
		$log = App_Log::getInstance(self::IDENT);
		$auth = Ini_Authentication::getInstance();
		$username = $this->getUsername();
		$auth = Ini_Authentication::getInstance();

		$tmpFilename = _ABSPATH.'/tmp/authentication.conf';
		$filename = _ABSPATH.'/etc/local/authentication.conf';

		$config = array(
			'auth' => array()
		);

		foreach ($auth->auth as $key => $type) {
			if ($type->adapter != 'Array') {
				continue;
			} else {
				$log->debug('Found a suitable Array adapter to change a password in');
			}

			switch($type->params->hashType) {
				case 'crypt':
					$hashedPassword = crypt($password, $type->params->users->$username);
					break;
				case 'none':
					$hashedPassword = $password;
					break;
				case 'md5':
					$hashedPassword = md5($password);
					break;
			}

			foreach($type->params->users as $user => $hash) {
				// Only change the password if the user is in the failsafe listing
				if ($user == $username) {
					$log->debug(sprintf('Failsafe configuration for %s was found', $user));

					$config['auth'] = array(
						$key => array(
							'name' => $type->name,
							'priority' => $type->priority,
							'adapter' => $type->adapter,
							'params' => array(
								'users' => array(
									$username => $hashedPassword
								),
								'hashType' => $type->params->hashType
							)
						)
					);

					break;
				} else {
					$log->debug(sprintf('Account %s is not configured for failsafe login', $username));
				}
			}
		}

		$oldAuth = new Zend_Config($auth->toArray(), true);
		$newAuth = new Zend_Config($config);

		$oldAuth->merge($newAuth);

		$config = array(
			'production' => $oldAuth->toArray()
		);

		$config = new Zend_Config($config);

		$writer = new Zend_Config_Writer_Ini(array(
			'config'   => $config,
			'filename' => $tmpFilename
		));
		$writer->write();

		@$result = copy($tmpFilename, $filename);

		if ($result === true) {
			unlink($tmpFilename);
			return true;
		} else {
			return false;
		}
	}

	protected function _getContacts($type = null) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$table = false;

		$sql = $db->select()
			->from('accounts_contacts')
			->where('account_id = ?', $this->_id)
			->order('id ASC');

		try {
			if (!is_null($type)) {
				$sql->where('type = ?', $type);
			}

			$log->debug($sql->__toString());

			$stmt = $db->query($sql);
			return $stmt->fetchAll();
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return array();
		}
	}

	public function addContact($contact, $type) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$validTypes = array('phone','im','email');

		$sql = array(
			'insert' => '	INSERT INTO %s (
						%s,%s,%s
					) VALUES (%s,%s,%s)'
		);

		if (empty($contact)) {
			return true;
		}

		if (!in_array($type, $validTypes)) {
			$log->err('The specified contact type was not valid');
			return false;
		}

		if ($this->contactExists($contact)) {
			$log->info('Contact already exists');
			return true;
		}

		try {
			$query = sprintf($sql['insert'],
				$db->quoteIdentifier('accounts_contacts'),
				$db->quoteIdentifier('account_id'),
				$db->quoteIdentifier('resource'),
				$db->quoteIdentifier('type'),
				$db->quote($this->_id),
				$db->quote($contact),
				$db->quote($type)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	public function deleteContact($id) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'delete' => 'DELETE FROM %s WHERE %s = %s'
		);

		if (empty($id) && !is_numeric($id)) {
			return true;
		}

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('accounts_contacts'),
				$db->quoteIdentifier('id'),
				$db->quote($id)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	public function updateContact($id, $resource) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s AND %s = %s'
		);

		try {
			$query = sprintf($sql['update'],
				$db->quoteIdentifier('accounts_contacts'),
				$db->quoteIdentifier('resource'),
				$db->quote($resource),
				$db->quoteIdentifier('id'),
				$db->quote($id),
				$db->quoteIdentifier('account_id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	public function contactExists($contact) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('accounts_contacts')
			->where('account_id = ?', $this->_id)
			->limit(1);

		if (is_numeric($contact)) {
			$sql->where('id = ?', $contact);
		} else {
			$sql->where('resource = ?', $contact);
		}

		try {
			$log->debug($sql->__toString());

			$stmt = $db->query($sql);

			if (count($stmt->fetchAll()) == 1) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	public function isFirstBoot() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('accounts')
			->where('id = ?', $this->_id)
			->where('firstboot = ?', '1');

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();

			if ($stmt->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}

	public function setFirstBoot($switch = 'off') {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$switch = strtolower($switch);

		$sql = array(
			'update' => 'UPDATE %s SET %s = %s WHERE %s = %s'
		);

		if ($switch == 'on') {
			$status = 1;
		} else {
			$status = 0;
		}

		try {
			$query = sprintf($sql['update'],
				$db->quoteIdentifier('accounts'),
				$db->quoteIdentifier('firstboot'),
				$db->quote($status),
				$db->quoteIdentifier('id'),
				$db->quote($this->_id)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Account_Exception($error->getMessage());
		}
	}
}

?>
