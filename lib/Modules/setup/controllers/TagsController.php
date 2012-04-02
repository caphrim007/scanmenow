<?php

/**
* @author Tim Rupp
*/
class Setup_TagsController extends Zend_Controller_Action {
	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$request = $this->getRequest();
		$config = Ini_Config::getInstance();

		if ($config->misc->firstboot == 0) {
			$redirector = $this->_helper->getHelper('Redirector');
			$redirector->gotoSimple('index', 'index', 'default');
		}

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName()
		));
	}

	public function indexAction() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$db = App_Db::getInstance($config->database->default);
		$permissions = new Permissions;

		$sql = $db->select()->from('tags');
		$stmt = $sql->query();
		$results = $stmt->fetchAll();

		foreach($results as $tag) {
			if (!$permissions->exists('Tag', $tag['id'])) {
				$log->debug(sprintf('Tag permission for "%s" does not exist. Creating it', $tag['name']));
				$result = $permissions->add('Tag', $tag['id']);
			} else {
				$log->debug('Tag permission already exists');
			}
		}
	}
}

?>
