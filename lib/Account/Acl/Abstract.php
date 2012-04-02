<?php

/**
* @author Tim Rupp
*/
abstract class Account_Acl_Abstract {
	protected $accountId;

	public function __construct($accountId) {
		$this->accountId = $accountId;
	}

	abstract function isAllowed($resource);
	abstract function get();
}

?>
