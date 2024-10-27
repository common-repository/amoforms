<?php
namespace Amoforms\Models\Entries;

use Amoforms\Models\Base_List\Base_List;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Entries_List
 * @since 2.8.0
 * @package Amoforms\Models\Entries
 */
class Entries_List extends Base_List implements Interfaces\Entries_List
{
	public function __construct()
	{
		parent::__construct();
		$this->_table = Table::instance();
	}

	public function get_collection()
	{
		$collection = new Collection();
		if ($all_db_params = $this->get()) {
			foreach ($all_db_params as $db_params) {
				$collection->add(new Entry($db_params));
			}
		}
		return $collection;
	}
}
