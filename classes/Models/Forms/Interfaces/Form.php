<?php
namespace Amoforms\Models\Forms\Interfaces;

use Amoforms\Models\Fields\Types\Interfaces\Base_Field;
use Amoforms\Models;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Interface Form
 * @package Amoforms\Models\Forms\Interfaces
 */
interface Form extends Models\Interfaces\Base
{
	public function set_default_fields();

	public function set_settings(array $settings);

	public function set_fields(array $fields_params);

	public function save();

	public function add_field(Base_Field $field);

	public function delete_field($id);

	/**
	 * Whether is form blocked
	 * @return bool
	 */
	public function is_blocked();

	/**
	 * Get param from settings by key
	 *
	 * @param string $key
	 * @param string $sub_key
	 *
	 * @return mixed
	 */
	public function get($key, $sub_key = NULL);

	public function get_settings();

	public function get_fields();

	public function get_title_types();

	public function get_confirmation_types();

	public function get_statuses_types();

	public function set_amo(array $values);
}
