<?php

/**
* Singleton class for returning a general logging
* object.
*
* @author Tim Rupp
*/
class App_Log {
	private static $config;
	private static $bfilter;

	const EMERG	= Zend_Log::EMERG;
	const ALERT 	= Zend_Log::ALERT;
	const CRIT 	= Zend_Log::CRIT;
	const ERR	= Zend_Log::ERR;
	const WARN 	= Zend_Log::WARN;
	const NOTICE 	= Zend_Log::NOTICE;
	const INFO 	= Zend_Log::INFO;
	const DEBUG 	= Zend_Log::DEBUG;

	public static function getInstance($ident = 'App') {
		$config	= Ini_Config::getInstance();
		$bFilter = new Zend_Filter_Boolean(Zend_Filter_Boolean::ALL);

		self::$config = $config;
		self::$bfilter = $bFilter;

		// The main Logger that all writers will be added to
		$log = new Zend_Log();

		/**
		* Add a null writer so that the logger will always have
		* something to write to
		*/
		$writer = new Zend_Log_Writer_Null();
		$log->addWriter($writer);

		// Configure global file logging
		$writer = App_Log::getGlobalFileWriter($ident);
		if ($writer !== false) {
			$log->addWriter($writer);
		}

		// Configure global stderr logging
		$writer = App_Log::getGlobalStderrWriter($ident);
		if ($writer !== false) {
			$log->addWriter($writer);
		}

		// Configure class level file logging
		$writer = App_Log::getClassFileWriter($ident);
		if ($writer !== false) {
			$log->addWriter($writer);
		}

		// Configure class level stderr logging
		$writer = App_Log::getClassStderrWriter($ident);
		if ($writer !== false) {
			$log->addWriter($writer);
		}

		return $log;
	}

	public static function getFilterFromMask($mask) {
		switch($mask) {
			case "err":
			case "error":
				$filter = new Zend_Log_Filter_Priority(self::ERR);
				break;
			case "warn":
			case "warning":
				$filter = new Zend_Log_Filter_Priority(self::WARN);
				break;
			case "info":
				$filter = new Zend_Log_Filter_Priority(self::INFO);
				break;
			case "debug":
			case "all":
			default:
				$filter = new Zend_Log_Filter_Priority(self::DEBUG);
				break;
		}

		return $filter;
	}

	public static function getMessagesFile() {
		$config	= self::$config;

		$logFile = $config->debug->log->messages;
		if (is_writeable(dirname($logFile)) && (file_exists($logFile) && is_writable($logFile))) {
			return $logFile;
		} else if (is_writeable(dirname($logFile)) && !file_exists($logFile)) {
			return $logFile;
		} else if (file_exists($logFile) && is_writable($logFile)) {
			return $logFile;
		} else {
			return false;
		}
	}

	public static function getGlobalFileWriter($ident) {
		$config	= self::$config;

		$globalMask = strtolower($config->debug->log->mask->global);
		$filter = App_Log::getFilterFromMask($globalMask);

		$logFile = App_Log::getMessagesFile();
		if ($logFile === false) {
			return false;
		}

		$writer = new Zend_Log_Writer_Stream($logFile);
		$formatter = new App_Log_Formatter_Default($ident);
		$writer->setFormatter($formatter);
		$writer->addFilter($filter);

		return $writer;
	}

	public static function getGlobalStderrWriter($ident) {
		$config	= self::$config;
		$bFilter = self::$bfilter;

		$globalMask = strtolower($config->debug->log->mask->global);
		$filter = App_Log::getFilterFromMask($globalMask);

		if ($bFilter->filter($config->debug->log->stderr->global)) {
			$writer = new Zend_Log_Writer_Stream('php://stderr');
			$formatter = new App_Log_Formatter_Default($ident);
			$writer->setFormatter($formatter);
			$writer->addFilter($filter);

			return $writer;
		} else {
			return false;
		}
	}

	public static function getClassFileWriter($ident) {
		$config	= self::$config;
		$bFilter = self::$bfilter;

		$classMask = strtolower($config->debug->log->mask->$ident);
		$filter = App_Log::getFilterFromMask($classMask);

		$logFile = App_Log::getMessagesFile();
		if ($logFile === false) {
			return false;
		}

		if ($bFilter->filter($config->debug->log->stderr->$ident)) {
			$writer = new Zend_Log_Writer_Stream($logFile);
			$formatter = new App_Log_Formatter_Default($ident);
			$writer->setFormatter($formatter);
			$writer->addFilter($filter);

			return $writer;
		} else {
			return false;
		}
	}

	public static function getClassStderrWriter($ident) {
		$config	= self::$config;
		$bFilter = self::$bfilter;

		$classMask = strtolower($config->debug->log->mask->$ident);
		$filter = App_Log::getFilterFromMask($classMask);

		if ($bFilter->filter($config->debug->log->stderr->$ident)) {
			$writer = new Zend_Log_Writer_Stream('php://stderr');
			$formatter = new App_Log_Formatter_Default($ident);
			$writer->setFormatter($formatter);
			$writer->addFilter($filter);

			return $writer;
		} else {
			return false;
		}
	}
}

?>
