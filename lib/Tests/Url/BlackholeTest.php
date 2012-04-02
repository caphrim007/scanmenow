<?php

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(dirname(dirname(__FILE__)))));
}

require _ABSPATH.'/lib/Autoload.php';

class Url_BlackholeTest extends PHPUnit_Framework_TestCase {
	/**
	* Runs the test methods of this class.
	*/
	public static function main() {
		require_once "PHPUnit/TextUI/TestRunner.php";

		$suite  = new PHPUnit_Framework_TestSuite("Url_BlackholeTest");
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	/**
	* @dataProvider providerDnsIncludes
	*/
	public function testUrlBlackholeAdd($domain) {
		$config = Ini_Config::getInstance('integration');

		$urlId = Url_Util::getId($domain);
		$url = new Url($urlId);
		$result = $url->blackhole->create();

		$this->assertEquals(true, $result);
	}

	/**
	* Removes a URL and restarts the DNS service
	*
	* @dataProvider providerDnsIncludes
	* @depends testUrlBlackholeAdd
	*/
	public function testUrlBlackholeRemove($domain) {
		$config = Ini_Config::getInstance('integration');

		$urlId = Url_Util::getId($domain);
		$url = new Url($urlId);
		$result = $url->blackhole->delete(true);

		$this->assertEquals(true, $result);
	}

	public function providerDnsIncludes() {
		$result = array(
			array('http://scanner-interface.com')
		);

		return $result;
	}
}

if (PHPUnit_MAIN_METHOD == "Url_BlackholeTest::main") {
	Url_BlackholeTest::main();
}

?>
