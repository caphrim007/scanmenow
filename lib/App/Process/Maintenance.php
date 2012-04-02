<?php

/**
* @author Tim Rupp
*/
class App_Process_Maintenance extends ZendX_Console_Process_Unix {
	const IDENT = __CLASS__;

	protected function _run() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$plugin = $this->getVariable('plugin');
		$params = $this->getVariable('params');

		$log->debug(sprintf('Startup of forked process using PID %s', $this->getPid()));

		try {
			$controller = new Maintenance_Engine;
			$controller->considerCron($params['considerCron']);
			$controller->registerPlugin($plugin);

			$log->debug('Dispatching the maintenance controller');
			$controller->dispatch();

			$log->debug('Maintenance finished. Notifying parent process of completion');
			$this->setVariable('isStopped', true);
		} catch (Exception $error) {
			$log->err($error->getMessage());
			$this->setVariable('isStopped', true);
		}
	}
}

?>
