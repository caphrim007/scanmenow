<?php

/**
* @author Tim Rupp
*/
class Maintenance_Plugin_CleanTokens extends Maintenance_Plugin_Abstract {
	const IDENT = __CLASS__;

	/**
	* @throws Maintenance_Plugin_Exception
	*/
	public function dispatch(Maintenance_Request_Abstract $request) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$date = new Zend_Date;

		$sql = array(
			'delete' => 'DELETE FROM %s WHERE %s <= %s'
		);

		try {
			$query = sprintf($sql['delete'],
				$db->quoteIdentifier('tokens'),
				$db->quoteIdentifier('valid_to'),
				$db->quote($date->get(Zend_Date::W3C))
			);

			$log->debug($query);

			$result = $db->query($query);
		} catch (Exception $error) {
			throw new Maintenance_Plugin_Exception($error->getMessage());
		}
	}
}

?>
