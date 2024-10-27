<?php
namespace Amoforms\Models\Fields\Types;

use Amoforms\Libs\Locale\I18n;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

/**
 * Class Total
 * @since 2.21.0
 * @package Amoforms\Models\Fields\Types
 */
class Total extends Base_Field
{
	protected function init()
	{
		$this->_type = self::TYPE_TOTAL;
		$this->_name = I18n::get('Total');
		$this->_options[self::OPTION_CURRENCY_SYMBOL] = '$';
		$this->_options[self::OPTION_CURRENCY_POSITION] = self::CURRENCY_SYMBOL_LEFT;
	}
}
