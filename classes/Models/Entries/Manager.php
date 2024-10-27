<?php
namespace Amoforms\Models\Entries;

use Amoforms\Exceptions\Runtime;
use Amoforms\Libs\Db\Db_Manager;
use Amoforms\Traits\Singleton;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Manager
 * @since 2.8.0
 * @method static $this instance
 * @package Amoforms\Models\Entries
 */
class Manager
{
	use Singleton;

	/** @var \wpdb */
	protected $_db;

	/** @var \Amoforms\Models\Entries\Table */
	protected $_table;

	protected $_fields = [
		'id',
		'form_id',
		'submit_date',
		'fields',
		'user_ip',
		'user_id',
		'user_name',
		'user_email',
	];

	protected function __construct()
	{
		$this->_db = Db_Manager::instance()->get_db();
		$this->_table = Table::instance();
	}

	/**
	 * Create table for entries
	 * @return $this
	 * @throws Runtime
	 */
	public function create_table()
	{
		$result = $this->_table->create();
		if (!$result) {
			throw new Runtime("Error creating table " . $this->_table->get_name());
		}
		return $this;
	}

	/**
	 * Get entries collection
	 * @return Collection
	 */
	public function get_collection()
	{
		$fields = implode(',', $this->_fields);
		$all_db_params = $this->_db->get_results("SELECT {$fields} FROM {$this->_table->get_name()}", ARRAY_A);

		$collection = new Collection();
		foreach ($all_db_params as $db_params) {
			$collection->add(new Entry($db_params));
		}

		return $collection;
	}

	/**
	 * Get Entry by id
	 * @param int $id
	 * @return Entry|FALSE
	 */
	public function get_by_id($id)
	{
		$fields = implode(',', $this->_fields);
		if (!$db_params = $this->_db->get_row("SELECT {$fields} FROM {$this->_table->get_name()} WHERE id=" . (int)$id, ARRAY_A)) {
			return FALSE;
		}
		return new Entry($db_params);
	}
}
