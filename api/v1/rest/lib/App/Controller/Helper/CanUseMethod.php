<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_CanUseMethod extends Zend_Controller_Action_Helper_Abstract {
	public function direct($accountId, $method) {
		$account = new Account($accountId);

		if ($account->acl->isAllowed('Api', $method)) {
			return true;
		} else if ($account->acl->isAllowed('Capability', 'use_any_api')) {
			return true;
		} else if ($account->acl->isAllowed('Capability', 'admin_operator')) {
			return true;
		} else {
			return false;
		}
	}
}

?>
