<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Select
 * @since 2.0.1
 * @package Amoforms\Models\Fields\Types
 */
class Select extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_SELECT;
		$this->_name = I18n::get('Select');
		$this->_is_enum = TRUE;
	}
}
