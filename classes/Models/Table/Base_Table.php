<?php
namespace Amoforms\Models\Table;

use Amoforms\Libs\Db\Db_Manager;
use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base_Table
 * @since 2.8.0
 * @package Amoforms\Models\Table
 */
abstract class Base_Table implements Interfaces\Base_Table
{
	use Singleton;

	/** @var \wpdb */
	protected $_db;

	/** @var string */
	protected $_base_name;

	/** @var string */
	protected $_name;

	/** @var string */
	protected $_primary_key = 'id';

	/** @var array */
	protected $_fields = [];

	/** @var array */
	protected $_keys = [];

	protected function __construct()
	{
		$this->_db = Db_Manager::instance()->get_db();
		$this->_name = $this->_db->prefix . $this->_base_name;
	}

	public function get_name() {
		return $this->_name;
	}

	public function get_fields() {
		return $this->_fields;
	}

	public function create()
	{
		$fields = implode(',', $this->get_fields_desc());
		if ($keys = implode(',', $this->get_keys_desc())) {
			$keys = ',' . $keys;
		}
		$query = "CREATE TABLE IF NOT EXISTS `{$this->_name}` (
		  {$fields},
		  PRIMARY KEY (`{$this->_primary_key}`)
		  {$keys}
		) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		return $this->_db->query($query);
	}

	public function drop()
	{
		return $this->_db->query("DROP TABLE IF EXISTS {$this->_name}");
	}

	public function check_fields() {
		$fields = $this->get_fields();
		$db_fields = $this->get_db_fields();

		$prev_field = $this->_primary_key;
		foreach ($fields as $field_name => $field) {
			if (!isset($db_fields[$field_name])) {
				$query = "ALTER TABLE `{$this->_name}` ADD COLUMN `{$field_name}` {$field['desc']} AFTER `{$prev_field}`;";
				$this->_db->query($query);
			}

			$prev_field = $field_name;
		}
	}

	public function get_db_fields() {
		$query = "SHOW COLUMNS FROM `{$this->_name}`;";
		$this->_db->query($query);
		$result = $this->_db->last_result;
		$db_fields = [];
		foreach ($result as $row) {
			$row = (array)$row;
			$db_fields[$row['Field']] = $row;
		}

		return $db_fields;
	}

	/**
	 * Get array of fields descriptions
	 * @return array
	 */
	protected function get_fields_desc()
	{
		$result = [];
		foreach ($this->_fields as $name => $params) {
			$result[] = "`{$name}` {$params['desc']}";
		}
		return $result;
	}

	/**
	 * Get array of keys descriptions
	 * @return array
	 */
	protected function get_keys_desc()
	{
		$result = [];
		foreach ($this->_keys as $key_name => $field_name) {
			$result[] = "KEY `{$key_name}` (`{$field_name}`)";
		}
		return $result;
	}
}
