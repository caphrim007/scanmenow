<?php

/**
* @author Tim Rupp
*/
class Bundle_Tag {
	protected $_tags;

	const IDENT = __CLASS__;

	public function __construct() {
		$this->reset();
	}

	public function startTime($time, $format = null) {
		if ($time instanceof Zend_Date) {
			$this->_data['start'] = $time;
		} else {
			if ($format === null) {
				$this->_data['start'] = new Zend_Date($time);
			} else {
				$this->_data['start'] = new Zend_Date($time, $format);
			}
		}
	}

	public function endTime($time, $format = null) {
		if ($time instanceof Zend_Date) {
			$this->_data['end'] = $time;
		} else {
			if ($format === null) {
				$this->_data['end'] = new Zend_Date($time);
			} else {
				$this->_data['end'] = new Zend_Date($time, $format);
			}
		}
	}

	public function expiresBefore($time, $format = null) {
		if ($time instanceof Zend_Date) {
			$this->_data['expireBefore'] = $time;
		} else {
			if ($format === null) {
				$this->_data['expireBefore'] = new Zend_Date($time);
			} else {
				$this->_data['expireBefore'] = new Zend_Date($time, $format);
			}
		}
	}

	public function expiresAfter($time, $format = null) {
		if ($time instanceof Zend_Date) {
			$this->_data['end'] = $time;
		} else {
			if ($format === null) {
				$this->_data['end'] = new Zend_Date($time);
			} else {
				$this->_data['end'] = new Zend_Date($time, $format);
			}
		}
	}

	public function filter($filter) {
		if (!in_array($filter, $this->_data['filters'])) {
			$this->_data['filters'][] = $filter;
		}
	}

	public function orFilter($filter) {
		if (!in_array($filter, $this->_data['orFilters'])) {
			$this->_data['orFilters'][] = $filter;
		}
	}

	public function reset($part = null) {
		if ($part === null) {
			$this->_data = array(
				'start' => null,
				'end' => null,
				'filters' => array(),
				'orFilters' => array(),
				'page' => 0,
				'limit' => 0
			);
		} else {
			switch($part) {
				case 'start':
				case 'end':
					$this->_data[$part] = null;
					break;
				case 'filters':
				case 'orFilters':
					$this->_data[$part] = array();
					break;
				case 'page':
					$this->_data['page'] = 0;
					break;
				case 'limit':
					$this->_data['limit'] = 0;
					break;
			}
		}
	}

	public function limit($limit) {
		if (is_numeric($limit)) {
			$this->_data['limit'] = $limit;
		}
	}

	public function page($page) {
		if (is_numeric($page)) {
			$this->_data['page'] = $page;
		}
	}

	public function __toString() {
		$sql = $this->_prepareQuery();
		return $sql->__toString();
	}

	public function getTags() {
		$log = App_Log::getInstance(self::IDENT);

		$sql = $this->_prepareQuery();
		$log->debug($sql->__toString());

		$stmt = $sql->query();
		$result = $stmt->fetchAll();

		return $result;
	}

	protected function _prepareQuery() {
		$config = Ini_Config::getInstance();
		$db = App_Db::getInstance($config->database->default);
		$tags = array();
		$orTags = array();
		$filters = array();
		$orFilters = array();
		$date = new Zend_Date;

		$sql = $db->select()
			->from('tags')
			->order('name ASC');

		if ($this->_data['start'] !== null) {
			$start = $this->_data['start'];
			$sql->where('created_at >= ?', $start->get(Zend_Date::W3C));
		}

		if ($this->_data['end'] !== null) {
			$end = $this->_data['end'];
			$sql->where('created_at <= ?', $end->get(Zend_Date::W3C));
		}

		$filters = array_unique($this->_data['filters']);
		if (!empty($filters)) {
			foreach($filters as $filter) {
				if (strpos($filter, '*') !== false) {
					$filter = str_replace('*','%',$filter);
				} else {
					$filter .= '%';
				}

				$sql->where('name LIKE ?', $filter);
			}
		}

		$orFilters = array_unique($this->_data['orFilters']);
		if (!empty($orFilters)) {
			foreach($orFilters as $filter) {
				if (strpos($filter, '*') !== false) {
					$filter = str_replace('*','%',$filter);
				} else {
					$filter .= '%';
				}

				$sql->orWhere('name LIKE ?', $filter);
			}
		}

		if (!empty($this->_data['limit'])) {
			$sql->limitPage($this->_data['page'], $this->_data['limit']);
		}

		return $sql;
	}

	public function count($limit = true) {
		$sql = $this->_prepareQuery();

		if ($limit === false) {
			$sql->limit(0);
		}

		$stmt = $sql->query();
		$result = $stmt->fetchAll();
		return count($result);
	}

	public function getNames() {
		return sort(array_values($this->_tags));
	}

	public function getIds() {
		return sort(array_keys($this->_tags));
	}
}

?>
