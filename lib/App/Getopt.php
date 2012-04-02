<?php

if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(dirname(dirname(__FILE__)))));
}

require_once _ABSPATH.'/lib/pear/Console/Getopt.php';

/**
* @author Tim Rupp
*/
class App_Getopt extends Zend_Console_Getopt {
	public function readPHPArgv() {
		global $argv;

		if (@$_SERVER['REQUEST_METHOD'] != '') {
			return PEAR::raiseError("Console_Getopt2: Web access is restricted");
		} else {
			return parent::readPHPArgv();
		}
	}
}

?>
