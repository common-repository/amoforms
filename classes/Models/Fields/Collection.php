<?php
namespace Amoforms\Models\Fields;

use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Validate;
use Amoforms\Models\Collection\Base_Collection;
use Amoforms\Models\Fields;
use Amoforms\Models\Fields\Types\Interfaces\Base_Field as Base_Field_Interface;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Collection of Fields
 * @since 1.0.0
 * @package Amoforms\Models\Forms
 */
class Collection extends Base_Collection implements Interfaces\Collection
{
	const PARAMS_KEY_MAX_ID = 'max_id';

	protected $_items_class = '\Amoforms\Models\Fields\Types\Interfaces\Base_Field';
	protected $_max_id = 0;

	/**
	 * Add field to collection
	 * @since 1.0.0
	 * @param Base_Field_Interface $field
	 * @return $this
	 * @throws Argument
	 */
	public function add($field)
	{
		$this->check_item_class($field);
		$this->check_field_id($field);
		return parent::add($field);
	}

	/**
	 * Insert item to specified position in collection
	 * @param Base_Field_Interface $field - object for insert
	 * @param int $pos - position in collection
	 * @return $this
	 */
	public function insert($field, $pos)
	{
		$this->check_item_class($field);
		$this->check_field_id($field);
		return parent::insert($field, $pos);
	}

	/**
	 * Delete field by id
	 * @param int $id
	 * @return $this
	 */
	public function delete($id)
	{
		if ($field = $this->get_by_id($id)) {
			$this->_data->detach($field);
		}
		return $this;
	}

	/**
	 * Duplicate field
	 * @param int $id
	 * @return Base_Field_Interface|bool
	 */
	public function duplicate($id)
	{
		$id = (int)$id;
		/** @var Base_Field_Interface $field */
		foreach ($this->_data as $index => $field) {
			if ($field->id() === $id) {
				$new_field = $field->get_clone();
				$this->insert($new_field, $index + 1);
				return $new_field;
			}
		}
		return FALSE;
	}

	/**
	 * Check field id existing.
	 * If id is not exists, it will be created
	 * @param Base_Field_Interface $field
	 */
	protected function check_field_id(Base_Field_Interface $field)
	{
		if (is_null($field->id())) {
			$field->id(++$this->_max_id);
		} elseif ($field->id() > $this->_max_id) {
			$this->_max_id = $field->id();
		}
	}

	/**
	 * Fill collection by array of fields params
	 * @since 1.0.0
	 * @param array $fields_params
	 * @return $this
	 * @throws Argument
	 * @throws Validate
	 */
	public function fill_by_params(array $fields_params)
	{
		$this->delete_all();
		$fields_manager = Manager::instance();

		if (!empty($fields_params['params'][self::PARAMS_KEY_MAX_ID])) {
			$this->_max_id = (int)$fields_params['params'][self::PARAMS_KEY_MAX_ID];
		}
		unset($fields_params['params']);

		foreach ($fields_params as $field_params) {
			if (empty($field_params['id'])) {
				throw new Validate('Empty field id');
			}
			if (empty($field_params['type'])) {
				throw new Validate('Empty field type');
			}
			$this->add($fields_manager->make_field($field_params['type'], $field_params));
		}

		return $this;
	}

	/**
	 * Get params of collections
	 * @since 2.16.0
	 * @return array
	 */
	protected function get_params()
	{
		return [
			self::PARAMS_KEY_MAX_ID => $this->_max_id,
		];
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	public function to_array()
	{
		$array = [];
		/** @var Base_Field_Interface $field */
		foreach ($this->_data as $field) {
			$array[] = $field->to_array();
		}
		return $array;
	}

	/**
	 * Get data for save to DB
	 * @since 2.16.0
	 * @return array
	 */
	public function get_for_save()
	{
		$fields = $this->to_array();
		$fields['params'] = $this->get_params();

		return $fields;
	}
}
