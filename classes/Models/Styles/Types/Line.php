<?php

namespace Amoforms\Models\Styles\Types;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Line extends Base_Style
{
	protected function init()
	{
		$this->_type = self::STYLE_TYPE_LINE;
		$this->_elements = [
			self::STYLE_TYPE_FIELD_WRAPPER => self::get_default_settings_field_wrapper(),
			self::STYLE_TYPE_FIELD_ELEMENT => self::get_default_settings_field_element(),
		];
	}

	protected static function get_default_settings_field_wrapper()
	{
		return [
			self::PROP_BACKGROUND_COLOR => 'transparent',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_HEIGHT           => '18px',
			self::PROP_PADDING          => '5px 0px 3px',
			self::PROP_MARGIN           => '0px',
		];
	}

	protected static function get_default_settings_field_element()
	{
		return [
			self::PROP_BACKGROUND_COLOR => '#9ca4ab',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_HEIGHT           => '1px',
			self::PROP_WIDTH            => '100%',
			self::PROP_PADDING          => '0px',
			self::PROP_MARGIN           => '0px',
		];
	}
}