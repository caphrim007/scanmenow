<?php

/**
* @author Tim Rupp
*/
class Table_Urls extends Zend_Db_Table_Abstract {
	protected $_name = 'urls';
	protected $_primary = 'id';
	protected $_sequence = 'urls_id_seq';
}

?>
