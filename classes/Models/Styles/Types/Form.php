<?php

namespace Amoforms\Models\Styles\Types;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Form extends Base_Style
{
	protected function init()
	{
		$this->_type = self::STYLE_TYPE_FORM;
		$this->_is_form = TRUE;
		$this->_elements = [
			self::STYLE_TYPE_FORM_CONTAINER => self::get_default_settings_form_container(),
			self::STYLE_TYPE_FORM_ROW       => self::get_default_settings_form_row()
		];
	}

	protected static function get_default_settings_form_container()
	{
		return [
			self::PROP_BACKGROUND_IMAGE => '',
			self::PROP_BACKGROUND_COLOR => '#FFFFFF',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_FONT_FAMILY      => 'PT Sans',
			self::PROP_PADDING          => '1px 0px 0px 0px',
			self::PROP_MARGIN           => '0px',
		];
	}

	protected static function get_default_settings_form_row()
	{
		return [
			self::PROP_BACKGROUND_COLOR => 'transparent',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_BORDER_STYLE     => 'dashed',
			self::PROP_BORDER_COLOR     => 'rgba(153, 155, 160, 0)',
			self::PROP_BORDER_WIDTH     => '1px',
			self::PROP_PADDING          => '15px 15px 15px 45px',
			self::PROP_MARGIN           => '5px 5px 0px 5px',
		];
	}

}