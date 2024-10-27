<?php
namespace Amoforms\Models\Styles;

use Amoforms\Models;
use Amoforms\Models\Collection\Base_Collection;
use Amoforms\Models\Styles;
use Amoforms\Models\Styles\Types\Interfaces\Base_Style as Base_Style_Interface;
use Amoforms\Exceptions\Argument;
use Amoforms\Exceptions\Validate;


defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Collection of Styles
 * @since 3.0.0
 * @package Amoforms\Models\Styles
 */
class Collection extends Base_Collection implements Interfaces\Collection
{
	const PARAMS_KEY_MAX_ID = 'max_id';

	protected $_items_class = '\Amoforms\Models\Styles\Types\Interfaces\Base_Style';
	protected $_max_id = 0;

	/**
	 * Add style to collection
	 * @since 3.0.0
	 * @param Base_Style_Interface $style
	 * @return $this
	 * @throws Argument
	 */
	public function add($style)
	{
		$this->check_item_class($style);
		$this->check_style_id($style);
		return parent::add($style);
	}

	/**
	 * Delete style by id
	 * @since 3.0.0
	 * @param int $id
	 * @return $this
	 */
	public function delete($id)
	{
		if ($style = $this->get_by_id($id)) {
			$this->_data->detach($style);
		}
		return $this;
	}


	/**
	 * Check style id existing.
	 * If id is not exists, it will be created
	 * @since 3.0.0
	 * @param Base_Style_Interface $style
	 */
	protected function check_style_id(Base_Style_Interface $style)
	{
		if (is_null($style->id())) {
			$style->id(++$this->_max_id);
		} elseif ($style->id() > $this->_max_id) {
			$this->_max_id = $style->id();
		}
	}

	/**
	 * Fill collection by array of styles params
	 * @since 3.0.0
	 * @param array $styles_params
	 * @return $this
	 * @throws Argument
	 * @throws Validate
	 */
	public function fill_by_params(array $styles_params)
	{
		$this->delete_all();
		$styles_manager = Manager::instance();

		if (!empty($styles_params['params'][self::PARAMS_KEY_MAX_ID])) {
			$this->_max_id = (int)$styles_params['params'][self::PARAMS_KEY_MAX_ID];
		}
		unset($styles_params['params']);

		foreach ($styles_params as $style_params) {
			if (empty($style_params['id'])) {
				throw new Validate('Empty style id');
			}
			if (empty($style_params['type'])) {
				throw new Validate('Empty style type');
			}
			$this->add($styles_manager->make_style($style_params['type'], $style_params));
		}
		return $this;
	}

	/**
	 * Get params of collections
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_params()
	{
		return [
			self::PARAMS_KEY_MAX_ID => $this->_max_id,
		];
	}

	/**
	 * @since 3.0.0
	 * @return array
	 */
	public function to_array()
	{
		$array = [];
		/** @var Base_Style_Interface $style */
		foreach ($this->_data as $style) {
			$array[] = $style->to_array();
		}
		return $array;
	}

	/**
	 * Get data for save to DB
	 * @since 3.0.0
	 * @return array
	 */
	public function get_for_save()
	{
		$styles = $this->to_array();
		$styles['params'] = $this->get_params();

		return $styles;
	}

	/**
	 * Get style by field ID
	 * @since 3.0.0
	 * @return Base_Style_Interface $style | bool
	 */
	public function get_by_element_id($id){
		$id = (int)$id;
		/** @var Base_Style_Interface $style */
		foreach ($this->_data as $style) {
			if($id == $style->get_object_id()){
				return $style;
			}
		}
		return FALSE;
	}

	/**
	 * Get styles collection by field type
	 * @since 3.0.0
	 * @return Base_Collection $styles_collection | bool
	 */
	public function get_by_type($type) {

		$styles_collection = $this->make_new_storage();

		/** @var Base_Style_Interface $style */
		foreach ($this->_data as $style) {
			if($type == $style->get_type()){
				$styles_collection->attach($style);
			}
		}
		return ($styles_collection->count() > 0) ? $styles_collection : FALSE;
	}

	/**
	 * Get max_id
	 * @since 3.1.1
	 * @return int
	 */
	public function get_max_id()
	{
		return $this->_max_id;

	}
}
