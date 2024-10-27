<?php

namespace Amoforms\Models\Styles\Types;

use Amoforms\Exceptions\Validate;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

abstract class Base_Style implements Interfaces\Base_Style
{

	/* CSS-properties*/
	const PROP_BACKGROUND_COLOR = 'background-color';
	const PROP_BACKGROUND_IMAGE = 'background-image';
	const PROP_BORDER_RADIUS = 'border-radius';
	const PROP_BORDER_WIDTH = 'border-width';
	const PROP_BORDER_STYLE = 'border-style';
	const PROP_BORDER_COLOR = 'border-color';
	const PROP_FONT_FAMILY = 'font-family';
	const PROP_BOX_SHADOW = 'box-shadow';
	const PROP_TEXT_COLOR = 'color';
	const PROP_FONT_SIZE = 'font-size';
	const PROP_FONT_WEIGHT = 'font-weight';
	const PROP_PADDING = 'padding';
	const PROP_MARGIN = 'margin';
	const PROP_HEIGHT = 'height';
	const PROP_WIDTH = 'width';
	/* end CSS-properties */

	/* Style types */
	const STYLE_TYPE_ADDRESS = 'address';
	const STYLE_TYPE_COMPANY = 'company';
	const STYLE_TYPE_EMAIL = 'email';
	const STYLE_TYPE_NAME = 'name';
	const STYLE_TYPE_PHONE = 'phone';
	const STYLE_TYPE_NUMBER = 'number';
	const STYLE_TYPE_URL = 'url';
	const STYLE_TYPE_TEXTAREA = 'textarea';
	const STYLE_TYPE_RADIO = 'radio';
	const STYLE_TYPE_SPECIAL = 'special';
	const STYLE_TYPE_DATE = 'date';
	const STYLE_TYPE_PAYMENT = 'payment';
	const STYLE_TYPE_RATING = 'rating';
	const STYLE_TYPE_LINE = 'line';
	const STYLE_TYPE_HEADING = 'heading';
	const STYLE_TYPE_TEXT = 'text';
	const STYLE_TYPE_SELECT = 'select';
	const STYLE_TYPE_MULTISELECT = 'multiselect';
	const STYLE_TYPE_CAPTCHA = 'captcha';
	const STYLE_TYPE_CHECKBOX = 'checkbox';
	const STYLE_TYPE_FILE = 'file';
	const STYLE_TYPE_INSTRUCTIONS = 'instructions';
	const STYLE_TYPE_SUBMIT = 'submit';
	const STYLE_TYPE_MODAL = 'modal';
	const STYLE_TYPE_ANTISPAM = 'antispam';
	const STYLE_TYPE_CITY = 'city';
	const STYLE_TYPE_COUNTRY = 'country';
	const STYLE_TYPE_STATE = 'state';
	const STYLE_TYPE_ZIPPOST = 'zippost';
	const STYLE_TYPE_TAX = 'tax';
	const STYLE_TYPE_TOTAL = 'total';
	/* end of Style types*/

	/* Elements */
	const STYLE_TYPE_FORM = 'form';
	const STYLE_TYPE_FORM_CONTAINER = 'form_container';
	const STYLE_TYPE_FORM_ROW = 'form_row';

	const STYLE_TYPE_FIELD_WRAPPER = 'field_wrapper';
	const STYLE_TYPE_FIELD_ELEMENT = 'field_element';
	const STYLE_TYPE_FIELD_LABEL = 'field_label';

	const STYLE_TYPE_RADIO_ITEM_ELEMENT = 'radio_item_element';
	const STYLE_TYPE_RADIO_ITEM_LABEL = 'radio_item_label';
	const STYLE_TYPE_RADIO_ITEM = 'radio_item';
	const STYLE_TYPE_RADIO_ITEM_SELECTED = 'radio_item_selected';

	const STYLE_TYPE_RATING_STAR_SELECT = 'rating_star_select';
	const STYLE_TYPE_RATING_STAR_HOVER = 'rating_star_hover';
	const STYLE_TYPE_RATING_STAR = 'rating_star';

	const STYLE_TYPE_SUBMIT_BUTTON = 'submit_button';
	const STYLE_TYPE_MODAL_BUTTON = 'modal_button';
	/* end of Elements */


	protected $_id;
	protected $_type;
	protected $_object_id = 0;
	protected $_elements = [];
	protected $_is_type_style = FALSE;

	public function __construct(array $db_params = NULL)
	{
		$this->init();

		if (!is_null($db_params)) {
			$this->set_db_params($db_params);
		}
	}

	protected abstract function init();

	/**
	 * Set params from database
	 * @since 3.0.0
	 * @param array $params
	 * @throws Validate
	 */
	protected function set_db_params(array $params)
	{
		if (empty($params['id'])) {
			throw new Validate('Empty style id');
		}
		$this->_id = abs((int)$params['id']);
		if(empty($params['object_id'])){
			$params['is_type_style'] = TRUE;
		}
		$this->set_params($params);
	}

	/**
	 * Set styles params
	 * @since 3.0.0
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
	 * @since 3.0.0
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
	 * @since 3.0.0
	 * @param int $id
	 * @return $this
	 */
	protected function set_object_id($id)
	{
		$this->_object_id = (int)$id;
		return $this;
	}

	/**
	 * @since 3.0.0
	 * @param bool $id
	 * @return $this
	 */
	protected function set_is_type_style($value)
	{
		$this->_is_type_style = (bool)$value;
		return $this;
	}

	/**
	 * @since 3.0.0
	 * @param array $options
	 * @return $this
	 */
	protected function set_elements($options)
	{
		if ($options && is_array($options)) {
			foreach (array_keys($this->_elements) as $key) {
				if (array_key_exists($key, $options)) {
					foreach ($options[$key] as $k => $v){
						if(in_array($k, $this->get_properties())){
							$value[$k] = $v;
						}
					}
					$this->_elements[$key] = $this->replace_empty($value);
				}
			}
		}
		return $this;
	}

	/**
	 * Replace empty values in styles
	 * @since 3.0.0
	 * @param array $elements
	 * @return array $elements
	 */
	protected function replace_empty($elements){
		if(is_array($elements)){
			foreach($elements as $element => &$value){
				if(empty($value)){
					switch ($element) {
						case self::PROP_BACKGROUND_COLOR:
						case self::PROP_BORDER_COLOR:
							$value = 'transparent';
							break;
						case self::PROP_TEXT_COLOR:
							$value = '#000000';
							break;
						case self::PROP_BOX_SHADOW:
							$value = 'none';
							break;
						case self::PROP_BORDER_RADIUS:
						case self::PROP_BORDER_WIDTH:
						case self::PROP_WIDTH:
						case self::PROP_HEIGHT:
						case self::PROP_MARGIN:
						case self::PROP_PADDING:
						case self::PROP_FONT_SIZE:
							$value = '0px';
							break;
						case self::PROP_FONT_FAMILY:
							$value = 'inherit';
							break;
						case self::PROP_BORDER_STYLE:
							$value = 'none';
					}
				}
			}
		}
		return $elements;
	}

	/**
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_system_params()
	{
		return [
			'id'   => $this->_id,
			'type' => $this->_type,
		];
	}

	/**
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_editable_params()
	{
		return [
			'object_id'     => $this->_object_id,
			'elements'      => $this->_elements,
			'is_type_style' => $this->_is_type_style,

		];
	}

	/**
	 * @since 3.0.0
	 * @return array of strings
	 */
	public static function get_styles_types()
	{
		return [
			self::STYLE_TYPE_ADDRESS,
			self::STYLE_TYPE_COMPANY,
			self::STYLE_TYPE_EMAIL,
			self::STYLE_TYPE_NAME,
			self::STYLE_TYPE_PHONE,
			self::STYLE_TYPE_NUMBER,
			self::STYLE_TYPE_URL,
			self::STYLE_TYPE_TEXTAREA,
			self::STYLE_TYPE_RADIO,
			self::STYLE_TYPE_SPECIAL,
			self::STYLE_TYPE_DATE,
			self::STYLE_TYPE_PAYMENT,
			self::STYLE_TYPE_RATING,
			self::STYLE_TYPE_LINE,
			self::STYLE_TYPE_HEADING,
			self::STYLE_TYPE_TEXT,
			self::STYLE_TYPE_SELECT,
			self::STYLE_TYPE_MULTISELECT,
			self::STYLE_TYPE_CAPTCHA,
			self::STYLE_TYPE_FILE,
			self::STYLE_TYPE_CHECKBOX,
			self::STYLE_TYPE_INSTRUCTIONS
		];
	}

	private static function get_properties()
	{
		return [
			self::PROP_BACKGROUND_COLOR,
			self::PROP_BACKGROUND_IMAGE,
			self::PROP_BORDER_RADIUS,
			self::PROP_BORDER_WIDTH,
			self::PROP_BORDER_STYLE,
			self::PROP_BORDER_COLOR,
			self::PROP_FONT_FAMILY,
			self::PROP_BOX_SHADOW,
			self::PROP_TEXT_COLOR,
			self::PROP_FONT_SIZE,
			self::PROP_FONT_WEIGHT,
			self::PROP_PADDING,
			self::PROP_MARGIN,
			self::PROP_HEIGHT,
			self::PROP_WIDTH
		];
	}

	/**
	 * Convers style model to array
	 * @since 3.0.0
	 * @return array
	 */
	public function to_array()
	{
		return $this->get_system_params() + $this->get_editable_params();
	}

	/**
	 * Get linked field id
	 * @since 3.0.0
	 * @return int
	 */
	public function get_object_id()
	{
		return $this->_object_id;
	}

	/**
	 * Get style type
	 * @since 3.0.0
	 * @return string
	 */
	public function get_type()
	{
		return $this->_type;
	}

	/**
	 * Check if is style for type
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_is_style_type()
	{
		return $this->_is_type_style;
	}

	/**
	 * Clone style
	 * @since 3.0.0
	 * @return Base_Style $new_style
	 */
	public function get_clone()
	{
		$new_style = clone $this;
		$new_style->_id = NULL;
		return $new_style;
	}
}