<?php

/**
* @author Tim Rupp
*/
class Role_Util {
	const IDENT = __CLASS__;

	public static function getRoles($page = 1, $limit = 15) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('roles')
			->order(array(
				'immutable DESC',
				'name ASC'
			)
		);

		if (!empty($limit)) {
			$sql->limitPage($page, $limit);
		}

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			return $stmt->fetchAll();
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return array();
		}
	}

	public static function exists($role) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('roles')
			->limit(1);

		if (is_numeric($role)) {
			$sql->where('id = ?', $role)
				->orWhere('name = ?', $role);
		} else {
			$sql->where('name = ?', $role);
		}

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$result = $stmt->fetchAll();

			if (count($result) > 0) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $error) {
			$log->err($error->getMessage());
			return false;
		}
	}

	public static function getId($name) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('roles', array('id'))
			->where('name = ?', $name);

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$result = $stmt->fetchAll();

			if (count($result) > 0) {
				return $result[0]['id'];
			} else {
				return 0;
			}
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}

	/**
	* @throws Role_Exception
	* @return integer
	*/
	public static function create($name = null, $description = null) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$date = new Zend_Date;

		$name = trim($name);

		$sql = array(
			'insert' => '	INSERT INTO %s (
						%s,%s,%s,%s
					) VALUES (
						%s,%s,%s,%s
					)'
		);

		try {
			$query = sprintf($sql['insert'],
				$db->quoteIdentifier('roles'),
				$db->quoteIdentifier('name'),
				$db->quoteIdentifier('description'),
				$db->quoteIdentifier('created'),
				$db->quoteIdentifier('last_modified'),
				$db->quote($name),
				$db->quote($description),
				$db->quote($date->get(Zend_Date::W3C)),
				$db->quote($date->get(Zend_Date::W3C))
			);

			$log->debug($query);

			$db->query($query);
			return $db->lastSequenceId('roles_id_seq');
		} catch (Exception $error) {
			throw new Role_Exception($error->getMessage());
		}
	}
}

?>
