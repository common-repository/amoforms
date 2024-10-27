<?php

namespace Amoforms\Models\Styles\Types;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

class Submit extends Base_Style
{
	protected function init()
	{
		$this->_type = self::STYLE_TYPE_SUBMIT;
		$this->_elements = [
			self::STYLE_TYPE_SUBMIT_BUTTON => self::get_default_settings_submit_button(),
		];
	}

	protected static function get_default_settings_submit_button()
	{
		return [
			self::PROP_BACKGROUND_COLOR => '#3F9DD4',
			self::PROP_BORDER_WIDTH     => '0px',
			self::PROP_BORDER_STYLE     => 'solid',
			self::PROP_BORDER_COLOR     => '#D4D5D8',
			self::PROP_BORDER_RADIUS    => '0px',
			self::PROP_TEXT_COLOR       => '#FFF',
			self::PROP_HEIGHT           => 'auto',
			self::PROP_FONT_SIZE        => '13px',
			self::PROP_FONT_FAMILY      => 'PT Sans',
			self::PROP_FONT_WEIGHT      => 'bold',
			self::PROP_PADDING          => '9px 39px',
			self::PROP_MARGIN           => '0px',
			self::PROP_WIDTH            => 'auto'
		];
	}
}