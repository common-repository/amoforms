<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Address
 * @since 1.1.0
 * @package Amoforms\Models\Fields\Types
 */
class Address extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_ADDRESS;
		$this->_name = I18n::get('Address');
		$this->_options[self::OPTION_NOAUTOFILL] = self::AUTOFILL_DISABLED;
		$this->_options[self::OPTION_LIMIT] = self::LIMIT_DEFAULT;
	}
}
