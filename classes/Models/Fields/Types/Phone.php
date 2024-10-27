<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Phone
 * @since 1.0.0
 * @package Amoforms\Models\Fields\Types
 */
class Phone extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_PHONE;
		$this->_name = I18n::get('Phone');
		$this->_placeholder = '+1 123 456 78 90';
		$this->_options[self::OPTION_USE_MASK] = self::MASK_DISABLED;
		$this->_options[self::OPTION_MASK_SYSTEM] = self::MASK_TYPE_PHONE;
		$this->_options[self::OPTION_MASK_CUSTOM] = self::MASK_DEFAULT;
		$this->_options[self::OPTION_MASK_DEFAULT] = self::MASK_DEFAULT;
		$this->_options[self::OPTION_NOAUTOFILL] = self::AUTOFILL_DISABLED;
		$this->_options[self::OPTION_LIMIT] = self::LIMIT_DEFAULT;
	}
}
