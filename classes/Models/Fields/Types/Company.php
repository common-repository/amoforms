<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Company
 * @since 1.1.0
 * @package Amoforms\Models\Fields\Types
 */
class Company extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_COMPANY;
		$this->_name = I18n::get('Company');
		$this->_placeholder = I18n::get('Company name');
		$this->_options[self::OPTION_NOAUTOFILL] = self::AUTOFILL_DISABLED;
		$this->_options[self::OPTION_LIMIT] = self::LIMIT_DEFAULT;
	}
}
