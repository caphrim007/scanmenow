<?php

/**
* @author Tim Rupp
*/
class Permissions_Capability extends Permissions_Abstract {
	const IDENT = __CLASS__;

	/**
	* @throws Permissions_Exception
	* @return boolean
	*/
	public function add($capability) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'insert' => 'INSERT INTO %s (%s) VALUES (%s)'
		);

		try {
			$query = sprintf($sql['insert'],
				$db->quoteIdentifier('permissions_capability'),
				$db->quoteIdentifier('resource'),
				$db->quote($capability)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Permissions_Exception($error->getMessage());
		}

		return false;
	}

	/**
	* @throws Permissions_Exception
	*/
	public function delete($id) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = array(
			'delete' => 'DELETE FROM %s WHERE %s = %s'
		);

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('permissions_capability'),
				$db->quoteIdentifier('id'),
				$db->quote($id)
			);

			$log->debug($query);

			$result = $db->query($query);
			return true;
		} catch (Exception $error) {
			throw new Permissions_Exception($error->getMessage());
		}
	}

	/**
	* @throws Permissions_Exception
	* @return boolean
	*/
	public function exists($capability) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select('id')
			->from('permissions_capability')
			->where(sprintf('%s = %s',
				$db->quoteIdentifier('resource'),
				$db->quote($capability)
			))
			->limit(1);

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
			throw new Permissions_Exception($error->getMessage());
		}
	}

	/**
	* @throws Permissions_Exception
	* @return array
	*/
	public function get($resource = null, $page = 1, $limit = 15) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('permissions_capability', array('permission_id' => 'id', 'permission_resource' => 'resource'));

		if ($resource === null) {
			$sql->order('resource ASC');
		} else {
			$sql->where('resource = ?', $resource);
		}

		if (!empty($limit)) {
			$sql->limitPage($page, $limit);
		}

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$result = $stmt->fetchAll();
			return $result;
		} catch (Exception $error) {
			throw new Permissions_Exception($error->getMessage());
		}
	}

	public function getPattern($resource = null, $pattern = null, $page = 1, $limit = 15) {

	}
}

?>
