<?php

/**
* @author Tim Rupp
*/
class Token_Util {
	const IDENT = __CLASS__;

	/**
	* @throws Token_Exception
	*/
	public static function create($account_id, $proxy_id) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$date = new Zend_Date;
		$validFrom = $date->get(Zend_Date::W3C);

		if (isset($config->tokens->timeout)) {
			$date->addSecond($config->tokens->timeout);
		} else {
			$date->addSecond(86400);
		}

		$validTo = $date->get(Zend_Date::W3C);

		$random = mt_rand(0, 255);
		$token = md5($account_id . $random);

		if (empty($_SERVER['REMOTE_ADDR'])) {
			$remote = '127.0.0.1';
		} else {
			$remote = $_SERVER['REMOTE_ADDR'];
		}

		$sql = array(
			'insert' => '	INSERT INTO %s (
						%s,%s,%s,
						%s,%s,%s
					) VALUES (%s,%s,%s,%s,%s,%s)'
		);

		try {
			$query = sprintf($sql['insert'],
				$db->quoteIdentifier('tokens'),
				$db->quoteIdentifier('account_id'),
				$db->quoteIdentifier('proxy_id'),
				$db->quoteIdentifier('token'),
				$db->quoteIdentifier('remote_address'),
				$db->quoteIdentifier('valid_from'),
				$db->quoteIdentifier('valid_to'),
				$db->quote($account_id),
				$db->quote($proxy_id),
				$db->quote($token),
				$db->quote($remote),
				$db->quote($validFrom),
				$db->quote($validTo)
			);

			$log->debug($query);
			$result = $db->query($query);
			return $token;
		} catch (Exception $error) {
			throw new Token_Exception($error->getMessage());
		}
	}

	/**
	* @throws Token_Exception
	*/
	public function read($accountId, $proxyId) {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);

		$valid = new Zend_Date;

		if (empty($_SERVER['REMOTE_ADDR'])) {
			$remote = '127.0.0.1';
		} else {
			$remote = $_SERVER['REMOTE_ADDR'];
		}

		$sql = $db->select()
			->from('tokens')
			->where('account_id = ?', $accountId)
			->where('proxy_id = ?', $proxyId)
			->where('remote_address = ?', $remote)
			->where('valid_from <= ?', $valid->get(Zend_Date::W3C))
			->where('valid_to >= ?', $valid->get(Zend_Date::W3C));

		try {
			$log->debug($sql->__toString());
			$stmt = $sql->query();
			$result = $stmt->fetchAll();

			if (empty($result)) {
				return array();
			} else {
				return $result[0]['token'];
			}
		} catch (Exception $error) {
			throw new Token_Exception($error->getMessage());
		}
	}
}

?>
