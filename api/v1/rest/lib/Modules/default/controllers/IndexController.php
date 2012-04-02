<?php

/**
* @author Tim Rupp
*/
class IndexController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
	}

	public function indexAction() {
		$this->view->status = 'ok';
		$this->view->message = 'Nobody here except us kittens';
	}
}

?>
