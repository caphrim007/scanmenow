<?php

/**
* @author Tim Rupp
*/
class Table_Tags extends Zend_Db_Table_Abstract {
	protected $_name = 'tags';
	protected $_primary = 'id';
	protected $_sequence = 'tags_id_seq';
}

?>
