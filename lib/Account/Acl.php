<?php

/**
* @author Tim Rupp
*/
class Account_Acl {
	/**
	* @var integer
	*/
	protected $accountId;

	/**
	* @var array
	*/
	protected $capability;

	/**
	* The primary role assigned to the account. This role
	* is what automated stuff usually gets assigned to.
	*/
	protected $primaryRole;

	/**
	* @var string
	*/
	const IDENT = __CLASS__;

	/**
	*
	*/
	public function __construct($accountId, $primaryRole = 0) {
		if (is_numeric($accountId)) {
			$this->accountId = $accountId;
		} else {
			$this->accountId = 0;
		}

		if (is_numeric($primaryRole)) {
			$this->primaryRole = $primaryRole;
		} else {
			$this->primaryRole = 0;
		}
		$this->acl = array();
	}

	/**
	* @throws Account_Acl_Exception
	* @return boolean
	*/
	public function isAllowed($type = null, $resource = null, $cache = true) {
		$result = false;

		if (is_array($resource)) {
			foreach($resource as $res) {
				if (isset($this->acl[$type][$res]) && $cache === true) {
					return $this->acl[$type][$res];
				}
			}
		}

		if (isset($this->acl[$type][$resource]) && $cache === true) {
			return $this->acl[$type][$resource];
		} else {
			$class = 'Account_Acl_'.$type;
			$permission = new $class($this->accountId);
		}

		if ($permission instanceof Account_Acl_Abstract) {
			$result = $permission->isAllowed($resource);

			if ($cache === true) {
				if (is_array($resource)) {
					foreach($resource as $res) {
						$this->capability[$type][$res] = $result;
					}
				} else {
					$this->capability[$type][$resource] = $result;
				}
			}

			return $result;
		} else {
			throw new Account_Acl_Exception('The supplied resource type is invalid');
		}
	}

	public function enumerate($type = null) {
		$results = array();
		$forbidden = array('Abstract', 'Broker', 'Exception', 'Test');

		if($type === null) {
			$dir = new DirectoryIterator(sprintf('%s/lib/Account/Acl', _ABSPATH));
			foreach($dir as $file ) {
				if(!$file->isDot() && !$file->isDir()) {
					$type = basename($file->getPathname(), '.php');
					$class = sprintf('Account_Acl_%s', $type);

					if (in_array($type, $forbidden)) {
						continue;
					}

					$permission = new $class($this->accountId);
					$results[$type] = $permission->get();

				}
			}

			return $results;
		} else {
			$class = 'Account_Acl_'.$type;
			$permission = new $class($this->accountId);
			$results[$type] = $permission->get();
			return $results;
		}
	}

	/**
	* @throws Account_Acl_Exception
	*/
	public function allow($permissionId) {
		$result = false;

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'insert' => 'INSERT INTO %s (%s,%s) VALUES (%s,%s)'
		);

		try {
			$query = sprintf($sql['insert'],
				$db->quoteIdentifier('roles_permissions'),
				$db->quoteIdentifier('permission_id'),
				$db->quoteIdentifier('role_id'),
				$db->quote($permissionId),
				$db->quote($this->primaryRole)
			);

			$log->debug($query);

			$db->query($query);
		} catch (Exception $error) {
			throw new Account_Acl_Exception($error->getMessage());
		}
	}
}

?>
