<?php
namespace Amoforms\Models\Table\Interfaces;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Base_Table
{
	/**
	 * Get table name
	 * @return string
	 */
	public function get_name();

	/**
	 * Get table fields (from object properties)
	 * @return array
	 */
	public function get_fields();

	/**
	 * Create table
	 * @return int|false
	 */
	public function create();

	/**
	 * Drop table
	 * @return int|false
	 */
	public function drop();

	/**
	 * Check fields for existing in database table.
	 * If field is not exists in table, it will be added.
	 * @since 2.9.0
	 * @return void
	 */
	public function check_fields();

	/**
	 * Get fields for database table.
	 * @since 2.9.0
	 * @return array
	 */
	public function get_db_fields();
}
