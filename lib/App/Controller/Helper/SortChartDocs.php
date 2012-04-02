<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_SortChartDocs extends Zend_Controller_Action_Helper_Abstract {
	public function direct($docs) {
		usort($docs, array($this, 'sortDocs'));
		return $docs;
	}

	protected function sortDocs($a, $b) {
		if ($a['value'] == $b['value']) {
			return 0;
		}

		return ($a['value'] < $b['value']) ? 1 : -1;
	}
}

?>
