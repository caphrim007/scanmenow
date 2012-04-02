<?php

/**
* @author Tim Rupp
*/
class Table_PoolsTags extends Zend_Db_Table_Abstract {
	protected $_name = 'pools_tags';
	protected $_primary = array('pool_id', 'tag_id');
}

?>
