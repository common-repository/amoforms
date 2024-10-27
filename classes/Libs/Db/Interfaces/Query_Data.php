<?php
namespace Amoforms\Libs\Db\Interfaces;

use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

interface Query_Data
{
	/**
	 * Set fields data
	 * @param string $key
	 * @param int|float|string $value
	 * @param string $format
	 * @return $this
	 * @throws Validate
	 */
	public function set($key, $value, $format);

	/**
	 * Set array of fields
	 * @param array $fields - ['field_name' => ['%s', 'value'], ...]
	 * @return $this
	 * @throws Validate
	 */
	public function set_array(array $fields);

	/**
	 * Get fields data
	 * @return array
	 */
	public function get_data();

	/**
	 * Get formats
	 * @return array
	 */
	public function get_formats();
}
