<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_ListMetrx extends Zend_Controller_Action_Helper_Abstract {
	public function direct($page = 1, $limit = 15, $type = 'charts') {
		$results = array();

		switch($type) {
			case 'charts':
			case 'tables':
			case 'reports':
				$results = $this->_listMetrx($type);
				break;
			default:
				break;
		}

		return $results;
	}

	protected function _listMetrx($type) {
		$results = array();
		$tmp = array();
		$dirDefault = sprintf('%/etc/default/%s/', _ABSPATH, $type);
		$dirLocal = sprintf('%/etc/local/%s/', _ABSPATH, $type);

		if (is_dir($dirDefault) && is_readable($dirDefault)) {
			$results = $this->_enumerateMetrx($dirDefault);
		}

		if (is_dir($dirLocal) && is_readable($dirLocal)) {
			$results = array_merge($results, $this->_enumerateMetrx($dirLocal));
		}

		/**
		* This filters out the duplicates that could be put
		* into the array from the merges above. It assumes
		* that the controller name is unique, which it is,
		* per metric type.
		*/
		if (!empty($results)) {
			foreach($results as $key => $result) {
				$controller = $result->controller;
				$tmp[$controller] = $result;
			}

			$results = array_values($tmp);
		}

		// Sorts the list of metrics by their label
		usort($results, array('App_Controller_Helper_ListMetrx', 'cmpLabels'));

		return $results;
	}

	protected function _enumerateMetrx($directory) {
		$result = array();
		$iter = new DirectoryIterator($directory);

		foreach ($iter as $controller) {
			if ($controller->isDot()) {
				 continue;
			}

			$fullpath = $controller->getRealPath();
			$filename = $controller->getFilename();

			if (substr($filename, 0, 1) == '.') {
				continue;
			}

			$conf = new Zend_Config_Ini($fullpath, 'config', array('allowModifications' => true));
			$conf->controller = basename($filename, '.conf');

			/**
			* This splits the Controller name so that it can be re-joined with
			* dashes so that ZF is able to reference it in URLs when I display it
			*
			* This regex will handle the following cases
			*
			* Only capital letters such as CountryExposure
			*
			*	array (
			*		0 => 'Country',
			*		1 => 'Exposure',
			*		2 => ''
			*	)
			*
			* Capital letters and numbers such as SplunkUrlInsertsLast24Hours
			*
			*	array (
			*		0 => 'Splunk',
			*		1 => 'Url',
			*		2 => 'Inserts',
			*		3 => 'Last',
			*		4 => '24',
			*		5 => 'Hours',
			*		6 => '',
			*	)
			*/
			preg_match_all('/([A-Z][^A-Z0-9]*|[0-9]*)/', basename($filename, '.conf'), $results);
			$results = array_filter($results[0]);

			$conf->controllerUrl = strtolower(implode('-', $results));

			$result[] = $conf;
		}

		return $result;
	}

	public static function cmpLabels($a, $b) {
		return strcmp($a->label, $b->label);
	}
}

?>
