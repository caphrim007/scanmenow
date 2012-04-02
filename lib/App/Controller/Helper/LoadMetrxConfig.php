<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_LoadMetrxConfig extends Zend_Controller_Action_Helper_Abstract {
	public function direct($metrx = null, $type = 'charts') {
		$results = array();

		$metrx = str_replace(array('Charts_', 'Reports_', 'Tables_', 'Controller'), '', $metrx);

		switch ($type) {
			case 'reports':
			case 'charts':
			case 'tables':
				$defaultIni = sprintf('%s/etc/default/%s/%s.conf', _ABSPATH, $type, $metrx);
				$localIni = sprintf('%s/etc/local/%s/%s.conf', _ABSPATH, $type, $metrx);
				break;
			default:
				return null;
		} 

		if (!file_exists($defaultIni)) {
			$default = new Zend_Config(array(),true);
		} else {
			$default = new Zend_Config_Ini(
				$defaultIni,
				null,
				array('allowModifications' => true)
			);
		}

		if (file_exists($localIni)) {
			$local = new Zend_Config_Ini(
				$localIni,
				null,
				array('allowModifications' => true)
			);

			$default->merge($local);
		}

		return $default->config;
	}
}

?>
