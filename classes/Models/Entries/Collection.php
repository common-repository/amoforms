<?php
namespace Amoforms\Models\Entries;

use Amoforms\Models\Collection\Base_Collection;
use Amoforms\Models\Entries\Interfaces\Entry;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Entries Collection
 * @since 2.8.0
 * @package Amoforms\Models\Forms
 */
class Collection extends Base_Collection implements Interfaces\Collection
{
	protected $_items_class = '\Amoforms\Models\Entries\Interfaces\Entry';

	public function add($item)
	{
		$this->check_item_class($item);
		parent::add($item);
	}

	public function to_array() {
		$array = [];
		/** @var Entry $entry */
		foreach ($this->_data as $entry) {
			$array[] = $entry->to_array();
		}
		return $array;
	}
}
