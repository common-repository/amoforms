<?php
namespace Amoforms\Models\Base_List;

use Amoforms\Libs\Db\Db_Manager;
use Amoforms\Models\Table\Interfaces\Base_Table;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base_List
 * @since 2.8.0
 * @package Amoforms\Models\Base_List
 */
abstract class Base_List implements Interfaces\Base_List
{
	/** @var \wpdb */
	protected $_db;

	/** @var Base_Table */
	protected $_table;

	/** @var array */
	protected $_filter = [];

	/** @var string */
	protected $_page_key = 'page_n';

	/** @var int */
	protected $_page = 1;

	/** @var int */
	protected $_offset = 0;

	/** @var int */
	protected $_limit = 20;

	public function __construct()
	{
		$this->_db = Db_Manager::instance()->get_db();
		$this->set_page_from_request();
	}

	public function set_page_from_request()
	{
		if (!empty($_GET[$this->_page_key])) {
			$this->set_page($_GET[$this->_page_key]);
		}
		return $this;
	}

	public function set_page($number)
	{
		$this->_page = abs((int)$number);
		$this->_offset = ($this->_page - 1) * $this->_limit;
		return $this;
	}

	public function get_page() {
		return $this->_page;
	}

	public function get_pages_count()
	{
		$row = $this->_db->get_row("SELECT count(*) as count FROM {$this->_table->get_name()} WHERE {$this->get_where()}", ARRAY_A);
		$count = isset($row['count']) ? (int)$row['count'] : 0;
		return (int)ceil($count / $this->_limit);
	}

	public function set_filter(array $filter = [])
	{
		$table_fields = $this->_table->get_fields();
		foreach ($filter as $field_name => $filter_value) {
			if (isset($table_fields[$field_name])) {
				$this->_filter[$field_name] = $filter_value;
			}
		}
		return $this;
	}

	public function get()
	{
		return $this->_db->get_results("SELECT * FROM {$this->_table->get_name()} WHERE {$this->get_where()} LIMIT {$this->_offset},{$this->_limit}", ARRAY_A) ?: [];
	}

	/**
	 * Get WHERE for query
	 * @return string
	 */
	protected function get_where()
	{
		$where = ' 1=1 ';
		foreach ($this->_filter as $field_name => $value) {
			$where .= " AND {$field_name}={$this->_db->_escape($value)} ";
		}
		return $where;
	}
}
