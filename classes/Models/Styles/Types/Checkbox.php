<?php

namespace Amoforms\Models\Styles\Types;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Checkbox extends Base_Style
{
	protected function init()
	{
		$this->_type = self::STYLE_TYPE_CHECKBOX;
		$this->_elements = [
			self::STYLE_TYPE_FIELD_ELEMENT => self::get_default_settings_special_field_element(),
			self::STYLE_TYPE_FIELD_LABEL   => self::get_default_settings_special_field_label(),
			self::STYLE_TYPE_FIELD_WRAPPER => self::get_default_settings_special_field_wrapper()
		];
	}

	protected static function get_default_settings_special_field_element()
	{
		return [
			self::PROP_BACKGROUND_COLOR => '#ffffff',
			self::PROP_BORDER_WIDTH     => '1px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#bcbec4',
			self::PROP_BORDER_RADIUS    => '2px',
			self::PROP_TEXT_COLOR       => '#000',
			self::PROP_HEIGHT           => '18px',
			self::PROP_WIDTH            => '18px',
			self::PROP_FONT_SIZE        => '15px',
			self::PROP_PADDING          => '0px',
			self::PROP_MARGIN           => '2px 0 0 0',
		];
	}

	protected static function get_default_settings_special_field_label()
	{
		return [
			self::PROP_BACKGROUND_COLOR => 'transparent',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_TEXT_COLOR       => '#313942',
			self::PROP_HEIGHT           => '20px',
			self::PROP_FONT_SIZE        => '15px',
			self::PROP_WIDTH            => 'auto',
			self::PROP_PADDING          => '0px',
			self::PROP_MARGIN           => '0px 10px 0px 0px',
		];
	}

	protected static function get_default_settings_special_field_wrapper()
	{
		return [
			self::PROP_BACKGROUND_COLOR => 'transparent',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_TEXT_COLOR       => '#313942',
			self::PROP_HEIGHT           => 'auto',
			self::PROP_WIDTH            => 'auto',
			self::PROP_PADDING          => '0px',
			self::PROP_MARGIN           => '0px',
		];
	}
}