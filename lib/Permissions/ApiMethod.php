<?php

/**
* @author Tim Rupp
*/
class Permissions_ApiMethod extends Permissions_Abstract {
	const IDENT = __CLASS__;

	public function add($resource) {
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
				$db->quoteIdentifier('permissions_api'),
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

	public function exists($resource) {
		return false;
	}

	/**
	* @throws Permissions_Exception
	*/
	public function get($resource = null, $page = 1, $limit = 15) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$sql = $db->select()
			->from('permissions_api', array('permission_id' => 'id', 'permission_resource' => 'resource'));

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
