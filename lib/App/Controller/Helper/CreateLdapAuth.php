<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_CreateLdapAuth extends Zend_Controller_Action_Helper_Abstract {
	public function direct($params) {
		$config = array(
			'auth' => array()
		);

		$auth = Ini_Authentication::getInstance()->toArray();

		$id = $params['id'];

		if (isset($auth['auth'])) {
			$keys = array_keys($auth['auth']);
		} else {
			$keys = array();
		}

		$params['accountFilterFormat'] = ereg_replace('[^A-Za-z0-9%()=!&-]', '', $params['accountFilterFormat']);
		$params['baseDn'] = ereg_replace('[^A-Za-z0-9,=-]', '', $params['baseDn']);

		if (isset($params['useSsl']) && empty($params['port'])) {
			$params['port'] = 636;
		}

		if (empty($params['port'])) {
			$params['port'] = 389;
		}

		$config['auth'] = array(
			$id => array(
				'name' => $params['auth-name'],
				'priority' => count($keys) + 1,
				'adapter' => 'Ldap',
				'params' => array(
					'host' => $params['host'],
					'port' => $params['port'],
					'baseDn' => $params['baseDn'],
					'accountFilterFormat' => $params['accountFilterFormat']
				)
			)
		);

		if (isset($params['useSsl'])) {
			$config['auth'][$params['auth-name']]['params']['useSsl'] = true;
		} else if (isset($params['useStartTls'])) {
			$config['auth'][$params['auth-name']]['params']['useStartTls'] = true;
		}

		if (isset($params['bindRequiresDn'])) {
			$config['auth'][$params['auth-name']]['params']['bindRequiresDn'] = true;
		}

		if (!empty($params['username'])) {
			$config['auth'][$params['auth-name']]['params']['username'] = $params['username'];
		}

		if (!empty($params['password'])) {
			$config['auth'][$params['auth-name']]['params']['password'] = $params['password'];
		}

		if (!empty($params['accountDomainName'])) {
			$config['auth'][$params['auth-name']]['params']['accountDomainName'] = $params['accountDomainName'];
		}

		return $config;
	}
}

?>
