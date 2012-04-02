<?php

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(dirname(dirname(dirname(__FILE__))))));
}

if (!defined("_MODPATH")) {
	define("_MODPATH", _ABSPATH.'/lib/Modules');
}

if (!defined("IDENT")) {
	define("IDENT", "Index");
}

require _ABSPATH.'/lib/Autoload.php';

class Modules_Settings_IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {
        public function setUp() {
		$view = new Zend_View;
		Zend_Session::$_unitTestEnabled = true;
		Zend_Session::start();

		$authSession = new Zend_Session_Namespace('Zend_Auth');
		$dataSession = new Zend_Session_Namespace(_APPLICATION);

		Zend_Registry::set('Zend_Auth', $authSession);
		Zend_Registry::set(_APPLICATION, $dataSession);

		$config = Ini_Config::getInstance();

		$front = $this->getFrontController();
		$front->throwExceptions(false);
		$front->addModuleDirectory(_MODPATH);

		$front->registerPlugin(new App_Controller_Plugin_CheckUnwritable());
		$front->registerPlugin(new App_Controller_Plugin_InitCache());
		$front->registerPlugin(new App_Controller_Plugin_Translate());
		$front->registerPlugin(new App_Controller_Plugin_Authentication());
		$front->registerPlugin(new App_Controller_Plugin_FirstBoot());
		$front->registerPlugin(new App_Controller_Plugin_CanLogin());

		if ($config->debug->log->mask == 'debug') {
			$front->registerPlugin(new App_Controller_Plugin_Profiling());
		}

		$view->addHelperPath(_MODPATH.'/default/views/helpers/', 'App_View_Helper');
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		Zend_Controller_Action_HelperBroker::addPath(_ABSPATH.'/lib/App/Controller/Helper', 'App_Controller_Helper');
        }

	public function loginUser() {
		$username = 'unittest';
		$password = 'Sarca$m0';

		$config = Ini_Config::getInstance();
		$ini = Ini_Authentication::getInstance();
		$auth = Zend_Auth::getInstance();
		$adapter = new App_Auth_Adapter_Multiple($ini, $username, $password);
		$auth->authenticate($adapter);

		$this->assertTrue($auth->hasIdentity());
	}

	public function testIndexAction() {
		$this->loginUser();
		$this->dispatch('/settings/modify/edit');

		$this->assertModule('settings', 'In Settings module');
		$this->assertController('modify', 'In the Modify controller');

		// print_r($this->getResponse());
		$this->assertQuery('.settings-block', 'Settings block exists');
	}
}

?>
