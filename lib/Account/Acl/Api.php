<?php

/**
* @author Tim Rupp
*/
class Account_Acl_Api extends Account_Acl_Abstract {
	const IDENT = __CLASS__;

	/**
	* @throws Account_Acl_Exception
	* @return boolean
	*/
	public function isAllowed($resource) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$constraints = array();

		$sql = $db->select('permissions_api.id')
			->from('accounts_roles', null)
			->join('roles_permissions',
				sprintf('%s.%s = %s.%s', 
					$db->quoteIdentifier('accounts_roles'),
					$db->quoteIdentifier('role_id'),
					$db->quoteIdentifier('roles_permissions'),
					$db->quoteIdentifier('role_id')
				),
				null
			)
			->join('permissions_api',
				sprintf('%s.%s = %s.%s', 
					$db->quoteIdentifier('roles_permissions'),
					$db->quoteIdentifier('permission_id'),
					$db->quoteIdentifier('permissions_api'),
					$db->quoteIdentifier('id')
				),
				array('id')
			)
			->where(sprintf('%s.%s = %s',
				$db->quoteIdentifier('accounts_roles'),
				$db->quoteIdentifier('account_id'),
				$db->quote($this->accountId)
			))
			->limit(1);

		if (is_array($resource)) {
			foreach($resource as $res) {
				if (substr($res, -1) == '*') {
					$res = substr($res, 0, -1);
					$constraints[] = sprintf('%s.%s LIKE %s',
						$db->quoteIdentifier('permissions_api'),
						$db->quoteIdentifier('resource'),
						$db->quote($res.'%')
					);
				}

				$constraints[] = sprintf('%s.%s = %s',
					$db->quoteIdentifier('permissions_api'),
					$db->quoteIdentifier('resource'),
					$db->quote($res)
				);
			}

			$tmp = implode(' OR ', $constraints);
			$sql->where($tmp);
		} else {
			if (substr($resource, -1) == '*') {
				$resource = substr($resource, 0, -1);
				$constraints[] = sprintf('%s.%s LIKE %s',
					$db->quoteIdentifier('permissions_api'),
					$db->quoteIdentifier('resource'),
					$db->quote($resource.'%')
				);
			}

			$constraints[] = sprintf('%s.%s = %s',
				$db->quoteIdentifier('permissions_api'),
				$db->quoteIdentifier('resource'),
				$db->quote($resource)
			);

			$tmp = implode(' OR ', $constraints);
			$sql->where($tmp);
		}

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$result = $stmt->fetchAll();

			if (count($result) == 1) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $error) {
			throw new Account_Acl_Exception($error->getMessage());
		}
	}

	public function get() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select('permissions_api.id')
			->from('accounts_roles')
			->join('roles_permissions',
				sprintf('%s.%s = %s.%s', 
					$db->quoteIdentifier('accounts_roles'),
					$db->quoteIdentifier('role_id'),
					$db->quoteIdentifier('roles_permissions'),
					$db->quoteIdentifier('role_id')
				),
				null
			)
			->join('permissions_api',
				sprintf('%s.%s = %s.%s', 
					$db->quoteIdentifier('roles_permissions'),
					$db->quoteIdentifier('permission_id'),
					$db->quoteIdentifier('permissions_api'),
					$db->quoteIdentifier('id')
				),
				array('id', 'resource')
			)
			->where(sprintf('%s.%s = %s',
				$db->quoteIdentifier('accounts_roles'),
				$db->quoteIdentifier('account_id'),
				$db->quote($this->accountId)
			));

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$result = $stmt->fetchAll();
			return $result;
		} catch (Exception $error) {
			throw new Account_Acl_Exception($error->getMessage());
		}
	}
}

?>
