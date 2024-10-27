<?php
namespace Amoforms\Models\Entries\Interfaces;

use Amoforms\Models\Base_List\Interfaces\Base_List;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Entries_List
 * @since 2.8.0
 * @package Amoforms\Models\Entries\Interfaces
 */
interface Entries_List extends Base_List
{
	/**
	 * Get Entries collection
	 * @since 2.8.0
	 * @return Collection
	 */
	public function get_collection();
}
