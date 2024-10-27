<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Number
 * @since 1.1.0
 * @package Amoforms\Models\Fields\Types
 */
class Number extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_NUMBER;
		$this->_name = I18n::get('Number');
		$this->_options[self::OPTION_NOAUTOFILL] = self::AUTOFILL_DISABLED;
		$this->_options[self::OPTION_LIMIT] = self::LIMIT_DEFAULT;
		$this->_options[self::OPTION_CALCULATION] = self::CALCULATION_FALSE_VALUE;
	}
}
