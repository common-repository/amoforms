<?php
namespace Amoforms\Models\Fields\Types\Interfaces;

use Amoforms\Interfaces\Array_Converting;
use Amoforms\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Base_Field
 * @since   1.0.0
 * @package Amoforms\Models\Fields\Types\Interfaces
 */
interface Base_Field extends Models\Interfaces\Base, Array_Converting
{
	/**
	 * Get or set id
	 * @since 2.0.1
	 * @param int $id
	 * @return $this
	 */
	public function id($id = NULL);

	/**
	 * Get clone of field with NULL id
	 * @since 2.0.1
	 * @return Base_Field
	 */
	public function get_clone();

	/**
	 * Set field params
	 * @since 1.0.0
	 * @param array $params
	 */
	public function set_params(array $params);

	/**
	 * @since 1.0.0
	 * @return array of strings
	 */
	public static function get_fields_types($category);
}
