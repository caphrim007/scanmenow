<?php

/**
* @author Tim Rupp
*/
class App_Controller_Plugin_JsonOutput extends Zend_Controller_Plugin_Abstract {
	public function postDispatch(Zend_Controller_Request_Abstract $request) {
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		$view = $viewRenderer->view;

		echo Zend_Json::encode(array(
			'status' => $view->status,
			'message' => $view->message
		));
	}
}

?>
