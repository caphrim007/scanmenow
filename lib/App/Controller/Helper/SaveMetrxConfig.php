<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_SaveMetrxConfig extends Zend_Controller_Action_Helper_Abstract {
	public function direct($metrx = null, $type = 'charts', Zend_Config $config, Zend_Config $changes) {
		$metrx = str_replace(array('Charts_', 'Reports_', 'Tables_', 'Controller'), '', $metrx);

		switch ($type) {
			case 'reports':
			case 'charts':
			case 'tables':
				$localIni = sprintf('%s/etc/local/%s/%s.conf', _ABSPATH, $type, $metrx);
				break;
			default:
				return false;
		}

		try {
			if (!is_writable($localIni)) {
				if (!is_writable(_ABSPATH.'/etc/local/')) {
					throw new Zend_Controller_Action_Exception('The location configuration directory is not writable');
				}
			} else if (file_exists($localIni) && !is_writable($localIni)) {
				throw new Exception('The local metric config file exists but is not writable');
			} else if (!is_writable($localIni)) {
				throw new Exception('The local metric config file is not writable');
			}

			$tmp = $config->toArray();
			foreach($changes as $key => $val) {
				$tmp[$key] = $val;
			}

			$result = array(
				'config' => $tmp
			);
			$config = new Zend_Config($result);
			$writer = new Zend_Config_Writer_Ini(array(
				'config'   => $config,
				'filename' => $localIni
			));
			$writer->write();

			return true;
		} catch (Exception $error) {
			return false;
		}
	}
}

?>
