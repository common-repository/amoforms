<?php
namespace Amoforms\Models\Fields\Types;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Email
 * @since 1.0.0
 * @package Amoforms\Models\Fields\Types
 */
class Email extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_EMAIL;
		$this->_name = 'Email';
		$this->_placeholder = 'yourmail@mail.com';
		$this->_options[self::OPTION_NOAUTOFILL] = self::AUTOFILL_DISABLED;
		$this->_options[self::OPTION_LIMIT] = self::LIMIT_DEFAULT;
	}
}
