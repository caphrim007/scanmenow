<?php

/**
* @author Tim Rupp
*/
class Maintenance_Plugin_Test_LongFunctional extends Maintenance_Plugin_Abstract {
	const IDENT = __CLASS__;

	public function maintenanceStartup(Maintenance_Request_Abstract $request) {
		$log = App_Log::getInstance(self::IDENT);
		$log->debug('Notified of maintenanceStartup');
	}

	public function dispatch(Maintenance_Request_Abstract $request) {
		$log = App_Log::getInstance(self::IDENT);
		$log->debug('Notified of dispatch; performing task');

		sleep(60);
	}

	public function maintenanceShutdown(Maintenance_Request_Abstract $request) {
		$log = App_Log::getInstance(self::IDENT);
		$log->debug('Notified of maintenanceShutdown');
	}
}

?>
