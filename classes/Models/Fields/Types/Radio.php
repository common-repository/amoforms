<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Radio
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Radio extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_RADIO;
		$this->_name = I18n::get('Radio');
		$this->_is_enum = TRUE;
		$this->_layout_edit = TRUE;
		$this->_layout = self::LAYOUT_HORIZONTAL;
	}
}
