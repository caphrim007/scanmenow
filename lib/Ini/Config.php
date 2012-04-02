<?php

/**
* @author Tim Rupp
*/
class Ini_Config {
	static $instance;

	public static function getInstance($instance = null) {
		if (empty(self::$instance)) {
			$ini = self::get($instance);
			self::$instance = $ini;
		}
		return self::$instance;
	}

	public static function get($instance = null) {
		$defaultIni = sprintf('%s/etc/default/config.conf', _ABSPATH);
		$localIni = sprintf('%s/etc/local/config.conf', _ABSPATH);

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

		if ($instance === null) {
			$instance = $default->config->instance;
		}

		if (isset($default->$instance)) {
			$default->$instance->instance = $instance;
			return $default->$instance;
		} else {
			throw new Exception(sprintf('Instance "%s" was not found in the configuration files', $instance));
		}
	}
}

?>
