<?php

/**
* @author Tim Rupp
*/
class Bundle_Url {
	protected $_data;

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
			$this->_data['expireAfter'] = $time;
		} else {
			if ($format === null) {
				$this->_data['expireAfter'] = new Zend_Date($time);
			} else {
				$this->_data['expireAfter'] = new Zend_Date($time, $format);
			}
		}
	}

	public function tag($tag) {
		if (!in_array($tag, $this->_data['tags'])) {
			$this->_data['tags'][] = $tag;
		}
	}

	public function account(Account $account) {
		$this->_data['account'] = $account->getId();
	}

	public function orTag($tag) {
		if (!in_array($tag, $this->_data['orTags'])) {
			$this->_data['orTags'][] = $tag;
		}
	}

	public function url($url) {
		$this->_data['urlName'] = $url;
	}

	public function pool($pool) {
		if (isset($this->_data['pools'][$pool])) {
			return;
		}

		$tagPool = new TagPool($pool);
		$tags = $tagPool->getTags();

		foreach($tags as $tag) {
			if (in_array($tag, $this->_tags)) {
				continue;
			} else {
				$this->_data['pools'][$pool][] = $tag;
			}
		}
	}

	public function reset($part = null) {
		if ($part === null) {
			$this->_data = array(
				'start' => null,
				'end' => null,
				'urlName' => '',
				'tags' => array(),
				'orTags' => array(),
				'pools' => array(),
				'orPools' => array(),
				'page' => 0,
				'limit' => 0,
				'account' => 0
			);
		} else {
			switch($part) {
				case 'start':
				case 'end':
					$this->_data[$part] = null;
					break;
				case 'urlName':
					$this->_data['urlName'] = '';
					break;
				case 'tags':
				case 'orTags':
				case 'pools':
				case 'orPools':
					$this->_data[$part] = array();
					break;
				case 'page':
					$this->_data['page'] = 0;
					break;
				case 'limit':
					$this->_data['limit'] = 0;
					break;
				case 'account':
					$this->_data['account'] = 0;
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

	public function getUrls() {
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
		$date = new Zend_Date;

		$subSelect = $db->select()
			->from(array('bt' => 'urls_tags'), null)
			->joinCross(array('b' => 'urls'), array('id'))
			->joinCross(array('t' => 'tags'), null)
			->where('bt.tag_id = t.id')
			->where('b.id = bt.url_id')
			->group('b.id');

		$sql = $db->select()
			->where('urls.expires >= ?', $date->get(Zend_Date::W3C));

		if ($this->_data['start'] !== null) {
			$start = $this->_data['start'];
			$sql->where('created_at >= ?', $start->get(Zend_Date::W3C));
		}

		if ($this->_data['end'] !== null) {
			$end = $this->_data['end'];
			$sql->where('created_at <= ?', $end->get(Zend_Date::W3C));
		}

		$tags = array_merge($tags, $this->_data['tags']);
		if (isset($this->_data['pools'])) {
			foreach($this->_data['pools'] as $pool) {
				$tags = array_merge($tags, $this->enumerateTags($pool));
			}
		}
		$tags = array_unique($tags);

		$orTags = array_merge($orTags, $this->_data['orTags']);
		if (isset($this->_data['orPools'])) {
			foreach($this->_data['orPools'] as $pool) {
				$orTags = array_merge($orTags, $this->enumerateTags($pool));
			}
		}
		$orTags = array_unique($orTags);


		/**
		* Ok there is some trickery going on here.
		*
		* These SQL queries start to get really really complicated
		* when you start doing WHERE this OR WHERE that AND NOT WHERE
		* this, etc. So I'm making an executive decision here and
		* saying that if you specify an orWhere tag, that I'm going
		* to kinda do a blanket OR tag|tag2|tag3. Where-as if you
		* don't specify an orWhere, I'm going to do a blanket
		* AND tag+tag2+tag3
		*/
		$allTags = array_merge($tags, $orTags);
		$allTags = array_unique($allTags);

		if (!empty($allTags)) {
			$subSelect->where('t.name IN (?)', $allTags);
		}

		// The having clause is only needed if you want to OR stuff together
		if (empty($orTags) && !empty($tags)) {
			$subSelect->having('COUNT(b.id) = ?', count($allTags));
		}

		if (!empty($this->_data['account']) && $this->_data['account'] != 0) {
			$subSelect->where('b.account_id = ?', $this->_data['account']);
		}

		if (!empty($this->_data['urlName'])) {
			$sql->where('uri LIKE ?', '%'.$this->_data['urlName'].'%');
		}

		$sql->from(array('urls_ids' => $subSelect), null)
			->joinLeft('urls', 'urls_ids.id = urls.id', array('uri','id','created_at','account_id','md5','title','ip_addr','expires'))
			->order('urls.created_at DESC');


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

	protected function enumerateTags($pool) {
		$tagPool = new TagPool($pool);
		return $tagPool->getTagIds();
	}

	public function enumerateIds() {
		$results = array();

		$sql = $this->_prepareQuery();
		$sql->limit(0);

		$sql->reset(Zend_Db_Select::COLUMNS);
		$sql->reset(Zend_Db_Select::FROM);
		$sql->reset(Zend_Db_Select::ORDER);

		$sql->from('urls', array('id'));
		$sql->order('id ASC');

		$stmt = $sql->query();
		$result = $stmt->fetchAll();

		foreach($result as $res) {
			$results[] = $res['id'];
		}

		return $results;
	}
}

?>
