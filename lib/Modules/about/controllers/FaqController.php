<?php

/**
* @author Tim Rupp
*/
class About_FaqController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$config = Ini_Config::getInstance();
		$request = $this->getRequest();

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
		));
	}

	public function indexAction() {

	}

	public function answerAction() {
		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);

		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$question = $request->getParam('question');

		$this->view->assign(array(
			'question' => $question
		));
	}
}

?>
