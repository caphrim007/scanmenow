<?php

/**
* @author Tim Rupp
*/
class Config_ModifyController extends Zend_Controller_Action {
	public $session;

	protected $_configFile;

	const IDENT = __CLASS__;

	public function init() {
		parent::init();

		$this->_configFile = _ABSPATH.'/etc/local/config.conf';

		$config = Ini_Config::getInstance();
		$auth = Zend_Auth::getInstance();

		$sessionUser = $auth->getIdentity();
		$sessionId = Account_Util::getId($sessionUser);
		$this->session = new Account($sessionId);
		$request = $this->getRequest();

		if ($this->session->isFirstBoot()) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('index', 'index', 'start');
		}

		if (!$this->session->acl->isAllowed('Capability', array('edit_config', 'admin_operator'))) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoSimple('permission-denied', 'error', 'default');
		}

		$this->view->assign(array(
			'action' => $request->getActionName(),
			'config' => $config,
			'controller' => $request->getControllerName(),
			'module' => $request->getModuleName(),
			'session' => $this->session
		));
	}

	public function editAction() {
		$result = array();

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$this->view->assign(array(
			'config' => $config
		));
	}

	public function saveAction() {
		$status = false;
		$message = null;
		$tmp = array();

		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$request = $this->getRequest();
		$request->setParamSources(array('_POST'));

		$params = $request->getParams();

		try {
			if (file_exists($this->_configFile) && !is_writable($this->_configFile)) {
				throw new Exception('The system config file exists but is not writable');
			} else if (!file_exists($this->_configFile) && !is_writable(dirname($this->_configFile))) {
				throw new Exception('The directory for the system config file is not writable');
			}

			$instance = $config->instance;
			unset($config->instance);

			$tmp[$instance] = $config->toArray();
			$tmp['config']['instance'] = $instance;

			@$tmp[$instance]['database']['default']['params']['host'] = $params['nq_host'];
			@$tmp[$instance]['database']['default']['params']['port'] = $params['nq_port'];
			@$tmp[$instance]['database']['default']['params']['dbname'] = $params['nq_dbname'];
			@$tmp[$instance]['database']['default']['params']['username'] = $params['nq_username'];
			@$tmp[$instance]['database']['default']['params']['password'] = $params['nq_password'];

			if ($this->session->acl->isAllowed('Capability', 'edit_miscomp')) {
				@$tmp[$instance]['database']['miscomp']['adapter'] = 'Pdo_Oci';
				@$tmp[$instance]['database']['miscomp']['params']['host'] = $params['mis_host'];
				@$tmp[$instance]['database']['miscomp']['params']['port'] = $params['mis_port'];
				@$tmp[$instance]['database']['miscomp']['params']['dbname'] = $params['mis_dbname'];
				@$tmp[$instance]['database']['miscomp']['params']['username'] = $params['mis_username'];
				@$tmp[$instance]['database']['miscomp']['params']['password'] = $params['mis_password'];
				@$tmp[$instance]['database']['miscomp']['params']['options']['caseFolding'] = 'lower';
			}

			@$tmp[$instance]['mail']['smtp']['server'] = $params['smtp_host'];
			@$tmp[$instance]['mail']['smtp']['params']['port'] = $params['smtp_port'];
			@$tmp[$instance]['mail']['smtp']['from'] = $params['smtp_from'];
			@$tmp[$instance]['mail']['smtp']['fromName'] = $params['smtp_from_name'];

			@$tmp[$instance]['service']['phishtank']['url'] = $params['ds_pt_url'];
			@$tmp[$instance]['service']['phishtank']['credentials'] = $params['ds_pt_creds'];
			@$tmp[$instance]['service']['malwareurl']['url'] = $params['ds_murl_url'];
			@$tmp[$instance]['service']['pglyoyohosts']['url'] = $params['ds_pgl_url'];
			@$tmp[$instance]['service']['virustotalhash']['url'] = $params['ds_vt_url'];
			@$tmp[$instance]['service']['virustotalhash']['selector'] = $params['ds_vt_selector'];
			@$tmp[$instance]['service']['malwaredomainlist']['url'] = $params['ds_mwdl_url'];
			@$tmp[$instance]['service']['mwpatrolconficker']['url'] = $params['ds_mwpc_url'];

			@$tmp[$instance]['xmpp']['default']['params']['host'] = $params['xmpp_host'];
			@$tmp[$instance]['xmpp']['default']['params']['port'] = $params['xmpp_port'];
			@$tmp[$instance]['xmpp']['default']['params']['username'] = $params['xmpp_username'];
			@$tmp[$instance]['xmpp']['default']['params']['password'] = $params['xmpp_password'];
			@$tmp[$instance]['xmpp']['default']['params']['resource'] = $params['xmpp_resource'];
			@$tmp[$instance]['xmpp']['default']['params']['server'] = $params['xmpp_server'];

			@$tmp[$instance]['expect']['path'] = $params['expect'];
			@$tmp[$instance]['python']['path'] = $params['python'];
			@$tmp[$instance]['java']['path'] = $params['java'];
			@$tmp[$instance]['gzip']['path'] = $params['gzip'];

			if ($this->session->acl->isAllowed('Capability', 'edit_nq_cstapi')) {
				@$tmp[$instance]['ws']['api']['nq']['uri'] = $params['nq_api_uri'];
				@$tmp[$instance]['ws']['api']['nq']['username'] = $params['nq_api_username'];
				@$tmp[$instance]['ws']['api']['nq']['password'] = $params['nq_api_password'];
			}

			if ($this->session->acl->isAllowed('Capability', 'edit_ws_cstapi')) {
				@$tmp[$instance]['ws']['api']['cstapi']['uri'] = $params['cstapi_uri'];
			}

			@$tmp[$instance]['ws']['api']['virustotal']['uri'] = $params['ws_vt_uri'];
			@$tmp[$instance]['ws']['api']['virustotal']['key'] = $params['ws_vt_key'];

			@$tmp[$instance]['blackhole']['redirect'] = $params['bh_redirect'];
			@$tmp[$instance]['blackhole']['limit'] = $params['bh_limit'];

			@$tmp[$instance]['google']['api']['maps']['key'] = $params['var_google_api_maps'];
			@$tmp[$instance]['google']['api']['safebrowsing']['key'] = $params['var_google_api_safebrowsing'];

			if ($params['exp_should'] == 'noexpire') {
				unset($tmp[$instance]['url']['expiration']);
				@$tmp[$instance]['url']['expiration']['should'] = $params['exp_should'];
			} else {
				@$tmp[$instance]['url']['expiration']['should'] = $params['exp_should'];
				@$tmp[$instance]['url']['expiration']['range'] = $params['exp_range'];
				@$tmp[$instance]['url']['expiration']['offset'] = $params['exp_offset'];
			}

			if ($params['exp_action'] == 'rm') {
				@$tmp[$instance]['url']['expiration']['action'] = 'rm';
			} else {
				@$tmp[$instance]['url']['expiration']['action'] = 'cmd';
				@$tmp[$instance]['url']['expiration']['command'] = $params['exportScriptPath'];
			}

			$newConfig = new Zend_Config($tmp);
			$writer = new Zend_Config_Writer_Ini(array(
				'config'   => $newConfig,
				'filename' => $this->_configFile
			));
			$writer->write();

			$status = true;
		} catch (Exception $error) {
			$log->err($error->getMessage());

			$status = false;
			$message = $error->getMessage();
		}

		$this->view->response = array(
			'status' => $status,
			'message' => $message
		);
	}
}

?>
