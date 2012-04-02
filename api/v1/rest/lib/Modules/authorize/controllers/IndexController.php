<?php

/**
* @author Tim Rupp
*/
class Authorize_IndexController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();

		throw new Exception('The requested API method in unavailable');
	}
}

?>
