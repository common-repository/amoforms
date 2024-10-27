<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Helpers\Strings;
use Amoforms\Libs\Locale\I18n;
use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Base_Field
 * @since 1.0.0
 * @package Amoforms\Models\Fields\Types
 */
abstract class Base_Field implements Interfaces\Base_Field
{
	const TYPE_HEADING = 'heading';
	const TYPE_NAME = 'name';
	const TYPE_PHONE = 'phone';
	const TYPE_EMAIL = 'email';
	const TYPE_COMPANY = 'company';
	const TYPE_TEXTAREA = 'textarea';
	const TYPE_TEXT = 'text';
	const TYPE_NUMBER = 'number';
	const TYPE_SELECT = 'select';
	const TYPE_MULTISELECT = 'multiselect';
	const TYPE_RADIO = 'radio';
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_DATE = 'date';
	const TYPE_URL = 'url';
	const TYPE_ADDRESS = 'address';
	const TYPE_FILE = 'file';
	const TYPE_INSTRUCTIONS = 'instructions';
	const TYPE_CAPTCHA = 'captcha';
	const TYPE_LINE = 'line';
	const TYPE_CITY = 'city';
	const TYPE_STATE = 'state';
	const TYPE_COUNTRY = 'country';
	const TYPE_ZIPPOST = 'zippost';
	const TYPE_ANTISPAM = 'antispam';
	const TYPE_RATING = 'rating';
	const TYPE_TAX = 'tax';
	const TYPE_TOTAL = 'total';

	const CATEGORY_TEMPLATE_FIELDS = 'Template Fields';
	const CATEGORY_USERINFO_FIELDS = 'User Information';
	const CATEGORY_SPECIAL_FIELDS = 'Special Fields';
	const CATEGORY_PAYMENT_FIELDS = 'Payment Fields';

	const DESCRIPTION_POS_BEFORE = 'before';
	const DESCRIPTION_POS_AFTER = 'after';
	const DESCRIPTION_POS_DEFAULT = self::DESCRIPTION_POS_AFTER;

	const MASK_DISABLED = 0;
	const MASK_SYSTEM = 1;
	const MASK_CUSTOM = 2;
	const MASK_DEFAULT = '00-00';
	const MASK_TYPE_PHONE = 'phone';
	const MASK_TYPE_DATE = 'date';
	const MASK_TYPE_EURO = 'euro';
	const MASK_TYPE_DOLLAR = 'dollar';

	const CAPTCHA_NTP_DISABLED = 0;
	const CAPTCHA_NTP_ENABLED = 1;
	const CAPTCHA_NTP_DEFAULT = self::CAPTCHA_NTP_DISABLED;

	const AUTOFILL_DISABLED = 0;
	const AUTOFILL_ENABLED = 1;
	const LIMIT_DEFAULT = '';

	const LAYOUT_VERTICAL = 'vertical';
	const LAYOUT_HORIZONTAL = 'horizontal';
	const LAYOUT_DEFAULT = self::LAYOUT_VERTICAL;

	const OPTION_USE_MASK = 'use_mask';
	const OPTION_SYSTEM_MASKS = 'system_masks';
	const OPTION_MASK_SYSTEM = 'mask-system';
	const OPTION_MASK_CUSTOM = 'mask-custom';
	const OPTION_MASK_DEFAULT = 'default_mask';
	const OPTION_USE_CAPTCHA_NTP = 'captcha_ntp';
	const OPTION_NOAUTOFILL = 'noautofill';
	const OPTION_LIMIT = 'limit';
	const OPTION_CALCULATION = 'calculation';
	const OPTION_CURRENCY_SYMBOL = 'curr_symbol';
	const OPTION_CURRENCY_POSITION = 'curr_position';

	const CALCULATION_FALSE_VALUE = 0;
	const CALCULATION_TRUE_VALUE = 1;

	const CURRENCY_SYMBOL_LEFT = 0;
	const CURRENCY_SYMBOL_RIGHT = 1;

	const CHECKBOX_TRUE_VALUE = 'Yes';
	const CHECKBOX_FALSE_VALUE = '';

	const CHECKBOX_API_TRUE_VALUE = 'on';
	const CHECKBOX_API_FALSE_VALUE = '';

	const FIELD_GRID_FULL = 0;
	const FIELD_GRID_HALF_LEFT = 1;
	const FIELD_GRID_HALF_RIGHT = 2;
	const FIELD_GRID_DEFAULT = self::FIELD_GRID_FULL;

	protected $_id;
	protected $_type;
	protected $_name = '';
	protected $_placeholder = '';
	protected $_label = '';
	protected $_default_value = '';
	protected $_description = '';
	protected $_extensions = '';
	protected $_hint = '';
	protected $_size = 1;
	protected $_description_position = self::DESCRIPTION_POS_DEFAULT;
	protected $_layout = self::LAYOUT_DEFAULT;
	protected $_layout_edit = FALSE;
	protected $_required = FALSE;
	protected $_read_only = FALSE;
	protected $_is_enum = FALSE;
	protected $_enums = [];
	protected $_options = [];
	protected $_grid = self::FIELD_GRID_DEFAULT;
	protected $_spam = [];


	public function __construct(array $db_params = NULL)
	{
		$this->init();
		if ($this->_is_enum) {
			$this->_enums = [
				'Option 1',
				'Option 2',
			];
		}
		if (!is_null($db_params)) {
			$this->set_db_params($db_params);
		}
	}

	/**
	 * Init field params
	 * @since 1.0.0
	 * @return void
	 */
	abstract protected function init();

	/**
	 * Get clone of field with NULL id
	 * @since 2.0.1
	 * @return Base_Field
	 */
	public function get_clone()
	{
		$new_field = clone $this;
		$new_field->_id = NULL;
		return $new_field;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_system_params()
	{
		return [
			'id'          => $this->_id,
			'type'        => $this->_type,
			'read_only'   => $this->_read_only,
			'is_enum'     => $this->_is_enum,
			'layout_edit' => $this->_layout_edit,
		];
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_editable_params()
	{
		return [
			'name'                 => $this->_name,
			'description'          => $this->_description,
			'description_position' => $this->_description_position,
			'default_value'        => $this->_default_value,
			'placeholder'          => $this->_placeholder,
			'required'             => $this->_required,
			'layout'               => $this->_layout,
			'enums'                => $this->_enums,
			'options'              => $this->_options,
			'label'                => $this->_label,
			'spam'                 => $this->_spam,
			'size'                 => $this->_size,
			'extensions'           => $this->_extensions,
			'hint'                 => $this->_hint,
			'grid'                 => $this->_grid,
		];
	}

	/**
	 * @since 2.21.0
	 * @return array
	 */
	public static function get_categories()
	{
		return [
			[
				'name'   => self::CATEGORY_TEMPLATE_FIELDS,
				'fields' => [
					self::TYPE_HEADING,
					self::TYPE_TEXTAREA,
					self::TYPE_TEXT,
					self::TYPE_NUMBER,
					self::TYPE_SELECT,
					self::TYPE_MULTISELECT,
					self::TYPE_RADIO,
					self::TYPE_CHECKBOX,
				]
			],
			[
				'name'   => self::CATEGORY_USERINFO_FIELDS,
				'fields' => [
					self::TYPE_NAME,
					self::TYPE_COMPANY,
					self::TYPE_PHONE,
					self::TYPE_EMAIL,
					self::TYPE_URL,
					self::TYPE_ADDRESS,
					self::TYPE_CITY,
					self::TYPE_STATE,
					self::TYPE_COUNTRY,
					self::TYPE_ZIPPOST,
				]
			],
			[
				'name'   => self::CATEGORY_SPECIAL_FIELDS,
				'fields' => [
					self::TYPE_CAPTCHA,
					self::TYPE_ANTISPAM,
					self::TYPE_FILE,
					self::TYPE_RATING,
					self::TYPE_LINE,
					self::TYPE_DATE,
					self::TYPE_INSTRUCTIONS,
				]
			],
			[
				'name'   => self::CATEGORY_PAYMENT_FIELDS,
				'fields' => [
					self::TYPE_TAX,
					self::TYPE_TOTAL,
				]
			]
		];
	}

	/**
	 * Get array representation of field
	 * @since 1.0.0
	 * @return array
	 */
	public function to_array()
	{
		return $this->get_system_params() + $this->get_editable_params();
	}

	/**
	 * @since 1.0.0
	 * @return array of strings
	 */
	public static function get_fields_types($category)
	{
		$result = [];
		foreach (self::get_categories() as $cat) {
			if($cat['name'] == $category){
				$result = $cat['fields'];
			}
		}
		return $result;
	}

	/**
	 * Get default masks list
	 * @since @2.19.19
	 * @return array
	 */
	public static function get_masks_list($key = NULL) {
		$result = [
			self::MASK_TYPE_PHONE  => '(000) 000-0000',
			self::MASK_TYPE_DATE   => '00/00/0000',
			self::MASK_TYPE_EURO   => '€0,00',
			self::MASK_TYPE_DOLLAR => '$0,00'
		];

		if (isset($key)) {
			$result = isset($result[$key]) ? $result[$key] : NULL;
		}

		return $result;
	}

	/**
	 * Get fields types with names
	 * @since 2.0.1
	 * @return array
	 */
	public static function get_fields_types_names($category)
	{
		$result = [];
		foreach (self::get_fields_types($category) as $type) {
			$result[$type] = I18n::get('field_type_' . $type);
		}
		return $result;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_description_positions()
	{
		return [
			self::DESCRIPTION_POS_BEFORE,
			self::DESCRIPTION_POS_AFTER,
		];
	}

	/**
	 * @since 2.0.1
	 * @return array
	 */
	protected function get_layouts_types()
	{
		return [
			self::LAYOUT_VERTICAL,
			self::LAYOUT_HORIZONTAL,
		];
	}

	/**
	 * Set params from database
	 * @since 2.9.0
	 * @param array $params
	 * @throws Validate
	 */
	protected function set_db_params(array $params)
	{
		if (empty($params['id'])) {
			throw new Validate('Empty field id');
		}
		$this->_id = abs((int)$params['id']);
		$this->set_params($params);
	}

	/**
	 * Set field params
	 * @since 1.0.0
	 * @param array $params
	 */
	public function set_params(array $params)
	{
		foreach ($this->get_editable_params() as $key => $value) {
			$method = "set_$key";
			if (isset($params[$key]) && method_exists($this, $method)) {
				$this->$method($params[$key]);
			}
		}
	}

	/**
	 * Get or set id
	 * @since 2.0.1
	 * @param int $id
	 * @return $this
	 */
	public function id($id = NULL)
	{
		if (is_null($id)) {
			return $this->_id;
		} elseif (is_null($this->_id)) {
			$this->_id = abs((int)$id);
		}
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param string $name
	 * @return $this
	 */
	protected function set_name($name)
	{
		$this->_name = Strings::escape($name);
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param string $description
	 * @return $this
	 */
	protected function set_description($description)
	{
		$this->_description = Strings::escape($description);
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param string $default_value
	 * @return $this
	 */
	protected function set_default_value($default_value)
	{
		$this->_default_value = Strings::escape($default_value);
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param string $placeholder
	 * @return $this
	 */
	protected function set_placeholder($placeholder)
	{
		$this->_placeholder = Strings::escape($placeholder);
		return $this;
	}

	/**
	 * @since 2.18.23
	 * @param string $label
	 * @return $this
	 */
	protected function set_label($label)
	{
		$this->_label = Strings::escape($label);
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param string $description_position
	 * @return $this
	 */
	protected function set_description_position($description_position)
	{
		if (in_array($description_position, $this->get_description_positions(), TRUE)) {
			$this->_description_position = $description_position;
		}
		return $this;
	}

	/**
	 * @since 1.0.0
	 * @param bool $value
	 * @return $this
	 */
	protected function set_required($value)
	{
		$this->_required = (bool)$value;
		return $this;
	}

	/**
	 * @since 2.0.1
	 * @param array $enums
	 * @return $this
	 */
	protected function set_enums($enums)
	{
		if ($this->_is_enum && is_array($enums)) {
			$this->_enums = [];
			foreach ($enums as $value) {
				if ($value = Strings::escape($value)) {
					$this->_enums[] = $value;
				}
			}
		}
		return $this;
	}

	/**
	 * @since 2.0.1
	 * @param string $layout
	 * @return $this
	 */
	protected function set_layout($layout)
	{
		if ($this->_layout_edit && in_array($layout, $this->get_layouts_types(), TRUE)) {
			$this->_layout = $layout;
		}
		return $this;
	}

	/**
	 * @since 2.11.9
	 * @param array $options
	 * @return $this
	 */
	protected function set_options($options)
	{
		if ($options && is_array($options)) {
			foreach (array_keys($this->_options) as $key) {
				if (array_key_exists($key, $options)) {
					$value = $options[$key];
					if ($key === self::OPTION_USE_CAPTCHA_NTP) {
						$value = $value ? 1 : 0;
					}
					$this->_options[$key] = $value;
				}
			}
		}
		return $this;
	}

	/**
	 * @since 2.21.0
	 * @param array $spam
	 * @return $this
	 */
	protected function set_spam($spam){
		if ($spam && is_array($spam)) {
			$this->_spam['question'] = $spam['question'];
			$this->_spam['answer'] = $spam['answer'];
		}
		return $this;
	}

	/**
	 * @since 2.19.14
	 * @param array $extensions
	 * @return $this
	 */
	protected function set_extensions($extensions)
	{
		$this->_extensions = Strings::escape($extensions);
		return $this;
	}

	/**
	 * @since 2.19.14
	 * @param string $size
	 * @return $this
	 */
	protected function set_size($size)
	{
		$this->_size = (float)Strings::escape($size);
		return $this;
	}

	/**
	 * @since @2.19.19
	 * @param string $hint
	 * @return $this
	 */
	protected function set_hint($hint)
	{
		$this->_hint = Strings::escape($hint);
		return $this;
	}

	/**
	 * @since @3.0.0
	 * @param string $grid
	 * @return $this
	 */
	protected function set_grid($grid)
	{
		if(in_array($grid, [self::FIELD_GRID_FULL, self::FIELD_GRID_HALF_LEFT, self::FIELD_GRID_HALF_RIGHT])){
			$this->_grid = $grid;
		}
		return $this;
	}
}
