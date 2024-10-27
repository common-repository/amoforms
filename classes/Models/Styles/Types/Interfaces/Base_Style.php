<?php
namespace Amoforms\Models\Styles\Types\Interfaces;

use Amoforms\Interfaces\Array_Converting;
use Amoforms\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Base_Style
 * @since 3.0.0
 * @package Amoforms\Models\Styles\Types\Interfaces
 */
interface Base_Style extends Models\Interfaces\Base, Array_Converting
{
	/**
	 * Get or set id
	 * @since 3.0.0
	 * @param int $id
	 * @return $this
	 */
	public function id($id = NULL);

	/**
	 * Get element id
	 * @since 3.0.0
	 * @return int
	 */
	public function get_object_id();

	/**
	 * Get style type
	 * @since 3.0.0
	 * @return string
	 */
	public function get_type();

	/**
	 * Clone style
	 * @since 3.0.0
	 * @return $this
	 */
	public function get_clone();

	/**
	 * Check if is style for type
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_is_style_type();

	/**
	 * Set style params
	 * @since 3.0.0
	 * @param array $params
	 */
	public function set_params(array $params);

	/**
	 * @since 3.0.0
	 * @return array of strings
	 */
	public static function get_styles_types();
}