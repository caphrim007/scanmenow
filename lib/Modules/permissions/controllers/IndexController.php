<?php

/**
* @author Tim Rupp
*/
class Permissions_IndexController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function indexAction() {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector'); 
		$redirector->gotoSimple('index', 'capability', 'permissions');
	}
}

?>
