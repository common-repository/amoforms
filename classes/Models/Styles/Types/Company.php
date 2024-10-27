<?php

namespace Amoforms\Models\Styles\Types;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Company extends Base_Style
{
	protected function init()
	{
		$this->_type = self::STYLE_TYPE_COMPANY;
		$this->_elements = [
			self::STYLE_TYPE_FIELD_WRAPPER => self::get_default_settings_field_wrapper(),
			self::STYLE_TYPE_FIELD_ELEMENT => self::get_default_settings_field_element(),
			self::STYLE_TYPE_FIELD_LABEL   => self::get_default_settings_field_label(),
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
			self::PROP_TEXT_COLOR       => '#313942',
			self::PROP_HEIGHT           => 'auto',
			self::PROP_FONT_SIZE        => '13px',
			self::PROP_PADDING          => '0px',
			self::PROP_MARGIN           => '0px',
		];
	}

	protected static function get_default_settings_field_label()
	{
		return [
			self::PROP_BACKGROUND_COLOR => 'transparent',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_TEXT_COLOR       => '#313942',
			self::PROP_HEIGHT           => 'auto',
			self::PROP_FONT_SIZE        => '15px',
			self::PROP_PADDING          => '11px 10px 0px 0px;',
			self::PROP_MARGIN           => '0px 10px 0px 0px',
		];
	}

	protected static function get_default_settings_field_element()
	{
		return [
			self::PROP_BACKGROUND_COLOR => '#FFFFFF',
			self::PROP_BORDER_WIDTH     => '1px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_TEXT_COLOR       => '#313942',
			self::PROP_HEIGHT           => '41px',
			self::PROP_FONT_SIZE        => '15px',
			self::PROP_PADDING          => '0px 10px',
			self::PROP_MARGIN           => '0px',
		];
	}

}