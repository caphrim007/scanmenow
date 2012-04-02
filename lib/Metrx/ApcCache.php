<?php

/**
* @author Tim Rupp
*/
class Metrx_ApcCache {
	const IDENT = __CLASS__;

	protected $_cache;
	protected $_mem;
	protected $_time;

	public function read() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);
		$time = time();
		$result = array();

		if(!function_exists('apc_cache_info')) {
			$log->err('APC caching functions were not found. APC does not appear to be running.');
			return array();
		}

		$cache = @apc_cache_info($cache_mode);
		if (empty($cache)) {
			$log->err('No cache info available. APC does not appear to be running.');
			return array();
		}

		$mem = apc_sma_info();
		if(!$cache['num_hits']) {
			$cache['num_hits'] = 1;
			$time++;
		}

		$this->_cache = $cache;
		$this->_mem = $mem;
		$this->_time = $time;

		$tmp = $this->_getFileCacheInformation();
		$result = array_merge($result, $tmp);

		$tmp = $this->_getMemoryUsage();
		$result = array_merge($result, $tmp);

		$tmp = $this->_getHitsAndMisses();
		$result = array_merge($result, $tmp);

		$tmp = $this->_getFragmentation();
		$result = array_merge($result, $tmp);

		return $result;
	}

	protected function _getFileCacheInformation() {
		$result = array(
			'requestRate' => '0.00',
			'hitRate' => '0.00',
			'missRate' => '0.00',
			'insertRate' => '0.00',
			'numberFiles' => 0,
			'cacheFullCount' => 0
		);

		/**
		* The number of cache requests per second
		*
		* This is the sum of the Hit Rate + the Miss Rate
		*/
		$requestRate = ($this->_cache['num_hits'] + $this->_cache['num_misses']) / ($this->_time - $this->_cache['start_time']);
		$result['requestRate'] = sprintf('%.2f', $requestRate);

		// The number of cache requests per second that
		// result in a cache hit
		$hitRate = ($this->_cache['num_hits']) / ($this->_time - $this->_cache['start_time']);
		$result['hitRate'] = sprintf('%.2f', $hitRate);

		// The number of cache requests per second that
		// result in a cache miss
		$missRate = ($this->_cache['num_misses']) / ($this->_time - $this->_cache['start_time']);
		$result['missRate'] = sprintf('%.2f', $missRate);

		// The rate, in requests per second, that new entries
		// are being put into the cache
		$insertRate = ($this->_cache['num_inserts']) / ($this->_time - $this->_cache['start_time']);
		$result['insertRate'] = sprintf('%.2f',$insertRate);

		// The number of files in the cache
		$result['numberFiles'] = $this->_cache['num_entries'];

		/** The number of times the cache was full and needed
		* to be cleared.
		*
		* This could be due to old entires in the cache remaining
		* around for too long, or a significant amount of re-caching
		* of changed files that causes the amount of memory available
		* for caching to become exhausted.
		*/
		$result['cacheFullCount'] = $this->_cache['expunges'];

		return $result;
	}

	protected function _getMemoryUsage() {
		$result = array(
			'memFreeInt' => 0,
			'memFreePercent' => 0,
			'memUsedInt' => 0,
			'memUsedPercent' => 0
		);

		$mem_size = $this->_mem['num_seg'] * $this->_mem['seg_size'];
		$mem_avail= $this->_mem['avail_mem'];
		$mem_used = $mem_size - $mem_avail;

		// Memory free, in bytes
		$result['memFreeInt'] = $mem_avail;

		// Memory free as a percentage of total memory allocated
		$result['memFreePercent'] = sprintf('%.1f', $mem_avail * 100 / $mem_size);

		// Memory used, in bytes
		$result['memUsedInt'] = $mem_used;

		// Memory used as a percentage of total memory allocated
		$result['memUsedPercent'] = sprintf('%.1f', $mem_used * 100 / $mem_size);

		return $result;
	}

	protected function _getHitsAndMisses() {
		$result = array(
			'hitInt' => 0,
			'hitPercent' => 0,
			'missInt' => 0,
			'missPercent' => 0
		);

		// Number of opcode cache hits
		$result['hitInt'] = $this->_cache['num_hits'];

		// Number of opcode cache hits as a percentage of total hits + misses
		$result['hitPercent'] = sprintf('%.1f', $this->_cache['num_hits'] * 100 / ($this->_cache['num_hits'] + $this->_cache['num_misses']));

		/**
		* Number of opcode cache misses
		*
		* This number, ideally, should be close to the number of
		* cached files. If the two numbers are close to each other,
		* that indicates that caching is effective, since the opcodes
		* are not having to be continually re-cached.
		*/
		$result['missInt'] = $this->_cache['num_misses'];

		// Number of opcode cache misses as a percentage of total hits + misses
		$result['missPercent'] = sprintf('%.1f', $this->_cache['num_misses'] * 100 / ($this->_cache['num_hits'] + $this->_cache['num_misses']));

		return $result;
	}

	protected function _getFragmentation() {
		$result = array(
			'fragmentation' => 0
		);

		// Fragementation: (freeseg - 1) / total_seg
		$nseg = 0;
		$freeseg = 0;
		$fragsize = 0;
		$freetotal = 0;

		for($i = 0; $i < $this->_mem['num_seg']; $i++) {
			$ptr = 0;
			foreach($this->_mem['block_lists'][$i] as $block) {
				if ($block['offset'] != $ptr) {
					++$nseg;
				}
				$ptr = $block['offset'] + $block['size'];
				/* Only consider blocks <5M for the fragmentation % */
				if($block['size']<(5*1024*1024)) $fragsize+=$block['size'];
				$freetotal+=$block['size'];
			}
			$freeseg += count($this->_mem['block_lists'][$i]);
		}

		if ($freeseg > 1) {
			$frag = sprintf('%.2f', ($fragsize / $freetotal)*100);
		} else {
			$frag = "0";
		}

		$result['fragmentation'] = $frag;

		return $result;
	}
}

?>
